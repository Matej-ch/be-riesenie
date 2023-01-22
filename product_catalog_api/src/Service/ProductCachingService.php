<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductCachingService
{
    public function __construct(private readonly ProductRepository      $productRepository,
                                private readonly TagAwareCacheInterface $productCache,
                                private readonly NormalizerInterface    $normalizer)
    {
    }

    public function get($id)
    {
        return $this->productCache->get("product_$id", function (ItemInterface $item) use ($id) {
            $item->expiresAfter(1800);
            $item->tag(['products', "product-$id"]);

            return $this->normalizer->normalize($this->productRepository->findOne($id), context: ['groups' => 'read']);
        });
    }
}