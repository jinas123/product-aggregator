<?php

namespace Jinas\Aggregator\Drivers;

use Clue\React\Buzz\Browser;
use Symfony\Component\DomCrawler\Crawler;
use Psr\Http\Message\ResponseInterface;
use React\Promise\PromiseInterface;
use Jinas\Aggregator\Models\Product;


class EatMvDriver implements IDriver
{
    private $browser;

    public function __construct(Browser $browser)
    {
        $this->browser = $browser;
    }

    public function scrape($url): PromiseInterface
    {
        return $this->browser->get($url)->then(function (ResponseInterface $response) {

            return $this->extract((string) $response->getBody());
        });
    }

    private function extract(string $responseBody)
    {
        $crawler = new Crawler($responseBody);

        if (!$crawler->filter('ol.products.list.items.product-items strong.product.name.product-item-name a.product-item-link')->first()->count() > 0) {
            return 0;
        }

        $title = $crawler->filter('ol.products.list.items.product-items strong.product.name.product-item-name a.product-item-link')->first()->text();
        $image = $crawler->filter('ol.products.list.items.product-items img.product-image-photo')->first()->attr('src');
        $price = str_replace("MVR", "", $crawler->filter('ol.products.list.items.product-items span.price')->first()->text());
        $url = $crawler->filter('ol.products.list.items.product-items a.product.photo.product-item-photo')->first()->attr('href');

        return new Product((string) $title, (string) $image, (int) $price, "Eatmv", (string) $url);
    }
}
