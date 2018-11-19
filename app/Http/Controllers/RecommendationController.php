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
    private $name_char_limit = 25;

    public function __construct()
    {
        $this->non_js_scraper = new Goutte();
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

    public function searchBukalapak()
    {
        return $this->getFromBukalapak(request('keyword'), 5);
    }

    public function searchElevenia()
    {
        return $this->getFromElevenia(request('keyword'), 5);
    }

    public function searchJdid()
    {
        return $this->getFromJdid(request('keyword'), 5);
    }

    public function getFromBukalapak($keyword = '', $limit = 5)
    {
        $crawler = $this->non_js_scraper->request(
            'GET',
            "https://www.bukalapak.com/products?utf8=%E2%9C%93&source=navbar&from=omnisearch&search_source=omnisearch_organic&search%5Bhashtag%5D=&search%5Bkeywords%5D=$keyword"
        );

        $products = collect();

        $crawler->filter('div.product-card')
            ->each(function ($product_card, $i) use($products, $limit) {

                if ($i + 1 > $limit) { return; }

                $id = (string) Str::orderedUuid();

                $name = $product_card->filter('article.product-display')->attr('data-name');
                $short_name = str_limit($name, $this->name_char_limit);

                $price = $product_card->filter('div.product-price')->attr('data-reduced-price');
                $price = "Rp." . number_format($price);
                
                $url = $product_card->filter('a.product-media__link')->link()->getUri();

                $rating_node = $product_card->filter('span.rating');
                $rating = $rating_node->count() > 0 ? (int) $rating_node->attr('title') : 0;
                
                $img_url = $product_card->filter('img.product-media__img')->attr('data-src');
                $products->push(compact('id', 'name', 'short_name', 'price', 'url', 'img_url', 'rating'));
        });

        $products->transform(function ($product) {
            $crawler = $this->non_js_scraper->request('GET', $product['url']);

            $sales_node = $crawler->filter('dd.c-deflist__value.qa-pd-sold-value');
            $product['sales'] = $sales_node->count() != 0 ? (int) trim($sales_node->text()) : 0;

            $product['source'] = 'Bukalapak';
            return $product;
        });

        return $products;
    }

    public function getFromElevenia($keyword = '', $limit = 5)
    {
        $crawler = $this->non_js_scraper->request(
            'GET',
            "http://www.elevenia.co.id/search?q=$keyword&lCtgrNo="
        );

        $products = collect();

        $crawler->filter('li.itemList')->each(function($product_card, $i) use($products, $limit) {
            if ($i > $limit) { return; }

            $name = $product_card->filter('a.pordLink')->text();
            $short_name = str_limit($name, $this->name_char_limit);

            $url = $product_card->filter('a.img')->link()->getUri();

            $img_url = $product_card->filter('a.img > img')->attr('src');

            $rating_node = $product_card->filter('span.rating');
            $rating = $rating_node->count() != 0 ? (int) substr($rating_node->attr('class'), -1) : 0;

            $id = (string) Str::orderedUuid();

            $products->push(compact('id', 'name', 'short_name', 'url', 'img_url', 'rating'));
        });

        $products->transform(function($product) {
            $crawler = $this->non_js_scraper->request('GET', $product['url']);
            $product['price'] = $crawler->filter('span.price')->text();

            $sales_node = $crawler->filter('span#reviewCount');
            $product['sales'] = $sales_node->count() != 0 ? (int) $sales_node->text() : 0;

            $product['source'] = 'Elevenia';
            return $product;
        });

        return $products;
    }

    public function getFromJdid($keyword = '', $limit = 5)
    {
        $crawler = $this->non_js_scraper->request(
            "GET",
            "https://www.jd.id/search?keywords=$keyword"
        );

        $products = collect();

        $crawler->filter('div.list-products-t div.item')->each(function ($product_card, $i) use($products, $limit) {
            if ($i + 1 > $limit) { return; }

            $name = $product_card->filter('a.name')->text();
            $short_name = str_limit($name, $this->name_char_limit);
            $price = $product_card->filter('div.p-price > span')->text();

            $rating = $product_card->filter('div.p-comstar-star a.active')->count();

            $sales_node = $product_card->filter('span.comstar-num');
            $sales = $sales_node->count() != 0 ? (int) $sales_node->text() : 0;

            $url = $product_card->filter('div.p-pic a')->link()->getUri();
            $img_url = $product_card->filter('div.p-pic a img')->image()->getUri();

            $products->push(compact('name', 'short_name', 'price', 'url', 'img_url', 'rating', 'sales'));
        });

        $products->transform(function($product) {
            $product['source'] = 'JD.id';
            return $product;
        });

        return $products;
    }
}
