<?php

namespace HPT;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerAwareTrait;

class Output implements OutputInterface
{
    use SerializerAwareTrait;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @inheritDoc
     */
    public function getJson(array $products): string
    {
        return $this->serializer->serialize($products, "json");
    }
}