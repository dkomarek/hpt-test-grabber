<?php

namespace HPT\Czc;

use Exception;
use HPT\GrabberInterface;
use Symfony\Component\DomCrawler\Crawler;

class Grabber implements GrabberInterface
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Parsuje data produktu z jeho HTML stranky na CZC
     * @param string $productId
     * @return Product|null
     * @throws Exception
     */
    public function grabProduct(string $productId): ?Product
    {
        // hledani dostupnych URL na produkt
        $productUrls = $this->searchProductUrls($productId);
        if (empty($productUrls)) {
            return null;
        }

        foreach($productUrls as $productUrl) {
            $productHtml = file_get_contents($this->getBaseUrl() . $productUrl);
            if ($productHtml === false) {
                throw new Exception(sprintf("Nelze načíst zdroj produktu dle URL %s", $productUrl));
            }

            $productCrawler = new Crawler($productHtml);

            // kontrola spravnosti kodu produktu
            if (!$this->checkProductCode($productCrawler, $productId)) {
                continue;
            }

            $product = new Product();

            // grab nazvu produktu
            $name = $this->searchProductName($productCrawler);
            if ($name !== null) {
                $product->setName($name);
            }

            // grab ceny produktu
            $price = $this->searchProductPrice($productCrawler);
            if ($price !== null) {
                $product->setPrice($price);
            }

            // grab hodnoceni produktu
            $rating = $this->searchProductRating($productCrawler);
            if ($rating !== null) {
                $product->setRating($rating);
            }

            return $product;
        }

        return null;
    }

    /**
     * Parsovani linku na detail produktu
     * @param string $productId
     * @return string[]
     * @throws Exception
     */
    private function searchProductUrls(string $productId): array
    {
        $searchUrl = $this->getSearchUrl($productId);

        $searchResultsHtml = file_get_contents($searchUrl);
        if ($searchResultsHtml === false) {
            throw new Exception(sprintf("Nelze načíst zdroj hledání dle URL %s", $searchUrl));
        }

        $crawler = new Crawler($searchResultsHtml);
        $productNodes = $crawler->filter($this->getFilterSearchResultItem());

        if ($productNodes->count() === 0) {
            return [];
        }

        return $productNodes->extract(["href"]);
    }

    /**
     * Parsovani ceny produktu
     * @param Crawler $productCrawler
     * @return float|null
     */
    private function searchProductPrice(Crawler $productCrawler): ?float
    {
        $productPriceNodes = $productCrawler->filter($this->getFilterProductPrice());
        if ($productPriceNodes->count() === 0) {
            return null;
        }

        return $this->parseFloatFromString($productPriceNodes->first()->text());
    }

    /**
     * Parsovani nazvu produktu
     * @param Crawler $productCrawler
     * @return string|null
     */
    private function searchProductName(Crawler $productCrawler): ?string
    {
        $productNameNodes = $productCrawler->filter($this->getFilterProductName());
        if ($productNameNodes->count() === 0) {
            return null;
        }

        return $productNameNodes->first()->text();
    }

    /**
     * Parsovani hodnoceni produktu
     * @param Crawler $productCrawler
     * @return float|null
     */
    private function searchProductRating(Crawler $productCrawler): ?float
    {
        $productRatingNodes = $productCrawler->filter($this->getFilterProductRating());
        if ($productRatingNodes->count() === 0) {
            return null;
        }

        return $this->parseFloatFromString($productRatingNodes->first()->text());
    }

    /**
     * Kontrola spravnosti kodu produktu
     * @param Crawler $productCrawler
     * @param string $productId
     * @return bool
     */
    private function checkProductCode(Crawler $productCrawler, string $productId): bool
    {
        $productCodeValues = $productCrawler->filter($this->getFilterProductCode())->extract(["_text"]);
        return in_array($productId, $productCodeValues, true);
    }

    /**
     * @param string $numericValue
     * @return float
     */
    private function parseFloatFromString(string $numericValue): float
    {
        $float = preg_replace('/([^0-9\.,])/i', '', $numericValue);
        return (float)str_replace(",", ".", $float);
    }

    // Konfigurace

    private function getBaseUrl(): string
    {
        return $this->config["base_url"];
    }

    private function getSearchUrl(string $productId): string
    {
        return $this->getBaseUrl() . str_replace("{PRODUCT_ID}", $productId, $this->config["search_url"]);
    }

    private function getFilterSearchResultItem(): string
    {
        return $this->config["filters"]["search_result_item"];
    }

    private function getFilterProductPrice(): string
    {
        return $this->config["filters"]["product_price"];
    }

    private function getFilterProductName(): string
    {
        return $this->config["filters"]["product_name"];
    }

    private function getFilterProductRating(): string
    {
        return $this->config["filters"]["product_rating"];
    }

    private function getFilterProductCode(): string
    {
        return $this->config["filters"]["product_code"];
    }
}