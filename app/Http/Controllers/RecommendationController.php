<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Goutte\Client as Goutte;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Illuminate\Support\Facades\Cache;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;

class RecommendationController extends Controller
{
    public function __construct()
    {
        $stack = HandlerStack::create();
        
        $stack->push(
            new CacheMiddleware(
              new PrivateCacheStrategy(
                new LaravelCacheStorage(
                  Cache::store('database')
                )
              )
            ),
            'cache'
        );

        $guzzle_client = new GuzzleClient(['handler' => $stack]);

        $this->scraper = new Goutte();
        $this->scraper->setClient($guzzle_client);
    }

    public function home()
    {
        return view('recommendation.home');
    }

    public function search()
    {
        $data = $this->validate(request(), [
            'keyword' => 'required|string'
        ]);

        $crawler = $this->scraper->request(
            'GET',
            "https://www.bukalapak.com/products?utf8=%E2%9C%93&source=navbar&from=omnisearch&search_source=omnisearch_organic&search%5Bhashtag%5D=&search%5Bkeywords%5D=$data[keyword]"
        );

        $products = collect();

        $crawler->filter('div.product-card')
            ->each(function ($product_card, $i) use($products) {

                if ($i > 5) {
                    return;
                }

                $name = $product_card
                    ->filter('article.product-display')
                    ->attr('data-name');

                $price = $product_card
                    ->filter('div.product-price')
                    ->attr('data-reduced-price');
                
                $url = $product_card
                    ->filter('a.product-media__link')
                    ->link()
                    ->getUri();
                
                $img_url = $product_card
                    ->filter('img.product-media__img')
                    ->attr('data-src');

                $sub_crawler = $this->scraper->request(
                    'GET', $url
                );

                $sales_count = $sub_crawler
                    ->filter('dd.c-deflist__value.qa-pd-seen-value.js-product-seen-value')
                    ->text();

                $sales_count = trim($sales_count);

                $products->push(compact('name', 'price', 'url', 'img_url', 'sales_count'));
        });

        $products = $products->sortByDesc('sales_count');

        return view('recommendation.search', compact('products'));
    }
}
