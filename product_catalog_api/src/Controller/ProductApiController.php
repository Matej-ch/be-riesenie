<?php

namespace App\Controller;


use App\Repository\ProductRepository;
use App\Service\ProductCacheInvalidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductApiController extends AbstractController
{
    private ProductRepository $productRepository;
    private TagAwareCacheInterface $productCache;

    public function __construct(ProductRepository $productRepository, TagAwareCacheInterface $productCache)
    {
        $this->productRepository = $productRepository;
        $this->productCache = $productCache;
    }

    #[Route('/api/products', methods: ['GET'])]
    public function getProducts(Request $request): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));


        //@TODO: here we should search product inside elasticSearch, instead only simple search on name, price an category name is implemented

        $paginator = $this->productRepository->getPaginator($request->query->all(), $offset);

        return $this->json([
            'products' => $paginator,
            'previous' => $offset - ProductRepository::PRODUCTS_PER_PAGE,
            'next' => min(count($paginator), $offset + ProductRepository::PRODUCTS_PER_PAGE),
        ], 200, [], ['groups' => ['read']]);
    }

    #[Route('/api/products/{id<\d+>}', methods: ['GET'])]
    public function getProduct(Request $request, NormalizerInterface $normalizer): Response
    {
        $id = $request->get('id');

        $product = $this->productCache->get("product_$id", function (ItemInterface $item) use ($id, $normalizer) {
            $item->expiresAfter(1800);
            $item->tag(['products', "product-$id"]);

            return $normalizer->normalize($this->productRepository->findOne($id), context: ['groups' => 'read']);
        });

        if (!$product) {
            return $this->json('"Product with id $id not found"', 404);
        }

        return $this->json($product, 200, [], ['groups' => ['read']]);
    }

    #[Route('/api/products', methods: ['POST'])]
    public function createProduct(): Response
    {

        return $this->json(['message' => 'this is post request']);
    }

    #[Route('/api/products/{id<\d+>}', methods: ['PATCH'])]
    public function updateProduct(Request $request): Response
    {
        $id = $request->get('id');

        $product = $this->productRepository->findOne($id);

        if (!$product) {
            return $this->json("Product with id $id not found", 404);
        }

        $product->setName($request->get('name'));
        $product->setPrice($request->get('price'));
        $this->productRepository->save($product, true);

        return $this->json($product, 200);
    }

    #[Route('/api/products/{id<\d+>}', methods: ['DELETE'])]
    public function deleteProduct(Request $request, ProductCacheInvalidator $productCacheInvalidator): Response
    {
        //@TODO here should be implemented delete request

        /** Invalidate cache for product based on tag */
        $productCacheInvalidator->removeProductCache($request->get('id'));

        /** Return 204 if successfully deleted*/
        /** Return 404 user calls delete on previously deleted product */
        return $this->json('Product deleted', 204);
    }
}