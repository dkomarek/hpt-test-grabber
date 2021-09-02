<?php

declare(strict_types=1);

namespace HPT;

use Exception;
use SplFileObject;

class Dispatcher
{
    /** @var GrabberInterface */
    private $grabber;

    /** @var OutputInterface */
    private $output;

    public function __construct(GrabberInterface $grabber, OutputInterface $output)
    {
        $this->grabber = $grabber;
        $this->output = $output;
    }

    /**
     * @return string JSON
     * @throws Exception
     */
    public function run(string $productsFile): string
    {
        $productCodes = $this->parseProductCodesFromFile($productsFile);
        $products = [];

        foreach($productCodes as $productCode) {
            $products[$productCode] = $this->grabber->grabProduct($productCode);
        }

        return $this->output->getJson($products);
    }

    /**
     * Dekodovani produktovych kodu ze vstupniho souboru
     * @param string $productsFile
     * @return array
     * @throws Exception
     */
    private function parseProductCodesFromFile(string $productsFile): array
    {
        $codes = [];

        if (!file_exists($productsFile)) {
            throw new Exception(sprintf("Soubor %s neexistuje", $productsFile));
        }

        $file = new SplFileObject($productsFile);

        while(!$file->eof()) {
            $code = trim($file->fgets());
            if (!empty($code)) {
                $codes[] = $code;
            }
        }

        $file = null;
        return $codes;
    }
}
