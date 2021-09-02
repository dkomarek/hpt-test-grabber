<?php

declare(strict_types=1);

namespace HPT;

use HPT\Czc\Product;

interface OutputInterface
{
    /**
     * @param Product[] $products
     * @return string
     */
    public function getJson(array $products): string;
}
