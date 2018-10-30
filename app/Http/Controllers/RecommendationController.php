<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Goutte\Client as Goutte;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Illuminate\Support\Facades\Cache;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;

use Symfony\Component\Panther\Client as Panther;

class RecommendationController extends Controller
{
    public function __construct()
    {
        $this->scraper = Panther::createChromeClient();
    }

    public function home()
    {
        return view('recommendation.home');
    }

    public function search()
    {
        $data = $this->validate(request(), [
            'keyword' => 'string|required'
        ]);

        return view('recommendation.search', ['keyword' => $data['keyword']]);
    }

    public function searchAll()
    {
        $products = collect();
        $products = $products->merge($this->getFromBukalapak(request('keyword'), 5));
        $products = $products->merge($this->getFromShopee(request('keyword'), 5));

        return $products;
    }

    public function searchBukalapak()
    {
        return $this->getFromBukalapak(request('keyword'), 5);
    }

    public function searchShopee()
    {
        return $this->getFromShopee(request('keyword'), 5);
    }

    public function getFromBukalapak($keyword = '', $limit = 5)
    {
        $crawler = $this->scraper->request(
            'GET',
            "https://www.bukalapak.com/products?utf8=%E2%9C%93&source=navbar&from=omnisearch&search_source=omnisearch_organic&search%5Bhashtag%5D=&search%5Bkeywords%5D=$keyword"
        );

        $products = collect();

        $crawler->filter('div.product-card')
            ->each(function ($product_card, $i) use($products, $limit) {

                if ($i + 1 > $limit) { return; }

                $id = (string) Str::orderedUuid();

                $name = $product_card
                    ->filter('article.product-display')
                    ->attr('data-name');

                $price = $product_card
                    ->filter('div.product-price')
                    ->attr('data-reduced-price');

                $price = "Rp." . number_format($price);
                
                $url = $product_card
                    ->filter('a.product-media__link')
                    ->link()
                    ->getUri();

                $rating_node = $product_card->filter('span.rating');
                $rating = $rating_node->count() > 0 ? $rating_node->attr('title') : 0;
                
                $img_url = $product_card
                    ->filter('img.product-media__img')
                    ->attr('data-src');

                $products->push(compact('id', 'name', 'price', 'url', 'img_url', 'rating'));
        });

        $products->transform(function ($product) {
            $crawler = $this->scraper->request('GET', $product['url']);

            $sales_node = $crawler->filter('dd.c-deflist__value.qa-pd-sold-value');
            $product['sales'] = $sales_node->count() != 0 ? (int) trim($sales_node->text()) : 0;

            $product['source'] = 'Bukalapak';
            return $product;
        });

        $products = $products->keyBy('id');
        $products = $products->sortByDesc('sales');
        return $products;
    }

    public function getFromShopee($keyword = '', $limit = 5)
    {
        $crawler = $this->scraper->request('GET', "https://shopee.co.id/search?keyword=$keyword");

        $products = collect();

        $this->scraper->waitFor('div.shopee-search-item-result__item');

        $crawler->filter('div.shopee-search-item-result__item')->each(function($product_card, $i) use($products, $limit) {
            // Don't process if exceeds limit
            if ($i + 1 > $limit) { return; }

            // Check if the product name is found, skip if not
            $name_node = $product_card->filter('div.shopee-item-card__text-name');
            if ($name_node->count() == 0) { ++$limit; return; }
            $name = trim($name_node->text());
            
            // Price
            $price_node = $product_card->filter('.shopee-item-card__current-price');
            if ($price_node->count() == 0) { ++$limit; return; }
            $price = $price_node->text();

            $url = $product_card
                ->filter('a.shopee-item-card--link')
                ->link()
                ->getUri();

            $this->scraper->waitFor('div.shopee-item-card__cover-img-background.animated-lazy-image__image--ready');

            $img_url_node = $product_card->filter('div.shopee-item-card__cover-img-background.animated-lazy-image__image--ready');
            if ($img_url_node->count() == 0) { ++$limit; return; }
            $img_url = $img_url_node->attr('style'); 
               
            // Obtain IMG URL
            $bg_image_str_pos = strpos($img_url, "background-image: ");
            $opening_quote_pos = strpos($img_url, "\"", $bg_image_str_pos);
            $closing_quote_pos = strpos($img_url, "\"", $opening_quote_pos + 1);
            $img_url = substr($img_url, $opening_quote_pos + 1, $closing_quote_pos - $opening_quote_pos - 1);


            // Generate id
            $id = (string) Str::orderedUuid();
    
            $products->push(compact("id", "name", "price", "url", "img_url", 'style'));
        });

        $products->transform(function ($product) {
            $crawler = $this->scraper->request('GET', $product['url']);
            $this->scraper->waitFor("div.flex.flex-auto._2uVI-L");
            $this->scraper->waitFor("div._3Vd3aw");

            $product['sales'] = trim($crawler
                ->filter('div._3Vd3aw')
                ->text());

            $rating_node = $crawler->filter('div._3d0_dh.EdxoqP');
            $product['rating'] = $rating_node->count() != 0 ? (int) trim($rating_node->text()) : 0;

            $product['source'] = 'Shopee';

            return $product;
        });

        $products = $products->keyBy('id');
        $products = $products->sortByDesc('sales');
        return $products;
    }
}
