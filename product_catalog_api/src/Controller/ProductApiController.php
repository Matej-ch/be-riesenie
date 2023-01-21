<?php

namespace App\Controller;


use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductApiController extends AbstractController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;

    }

    #[Route('/api/products/', methods: ['GET'])]
    public function getProducts()
    {
        $products = $this->productRepository->findAll();

        return $this->json([
            'products' => $products
        ], 200, [], ['groups' => ['read']]);
    }

    #[Route('/api/products/{id<\d+>}', methods: ['GET'])]
    public function getProduct(int $id): Response
    {

        $product = $this->productRepository->findOne($id);

        return $this->json($product, 200, [], ['groups' => ['read']]);
    }
}