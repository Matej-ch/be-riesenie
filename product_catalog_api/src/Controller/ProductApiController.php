<?php

namespace App\Controller;


use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductApiController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route('/api/products', methods: ['GET'])]
    public function getProducts(): Response
    {
        $products = $this->productRepository->findAll();

        return $this->json([
            'products' => $products
        ], 200, [], ['groups' => ['read']]);
    }

    #[Route('/api/products/{id<\d+>}', methods: ['GET'])]
    public function getProduct(Request $request): Response
    {
        $id = $request->get('id');

        $product = $this->productRepository->findOne($id);

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
    public function deleteProduct(): Response
    {
        //@TODO here should be implemented delete request
        //@TODO remove product from cache also

        /** Return 204 if successfully deleted*/
        /** Return 404 user calls delete on previously deleted product */
        return $this->json('Product deleted', 204);
    }
}