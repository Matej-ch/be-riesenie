<?php

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductCacheInvalidator
{
    public function __construct(private readonly TagAwareCacheInterface $productCache)
    {
    }

    public function removeProductCache(int $id): void
    {
        $this->productCache->invalidateTags(["product-$id"]);
    }

    /**
     * Useful method when we want to invalidate entire cache
     *
     * For example, we have console command that refreshes products in cache every night
     * Or we have new version of api
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function removeProductsCache(): void
    {
        $this->productCache->invalidateTags(['products']);
    }
}