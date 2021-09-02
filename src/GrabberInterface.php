<?php

declare(strict_types=1);

namespace HPT;

use HPT\Czc\Product;

interface GrabberInterface
{
    public function grabProduct(string $productId): ?Product;
}
