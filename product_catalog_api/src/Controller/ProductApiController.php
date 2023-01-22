<?php

namespace App\Controller;


use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ProductCacheInvalidator;
use App\Service\ProductCachingService;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductApiController extends ApiController
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route('/api/products', methods: ['GET'])]
    public function getProducts(Request $request): Response
    {
        //@TODO: here we should search product inside elasticSearch, instead only simple search on name, price an category name is implemented

        $queryBuilder = $this->productRepository->getPaginator($request->query->all());
        $adapter = new QueryAdapter($queryBuilder);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, $request->query->get('page', 1), $this->productRepository::PRODUCTS_PER_PAGE);

        return $this->json($pager, Response::HTTP_OK, [], ['groups' => ['read']]);
    }

    #[Route('/api/products/{id<\d+>}', methods: ['GET'])]
    public function getProduct(Request $request, ProductCachingService $productCachingService): Response
    {
        $id = $request->get('id');

        $product = $productCachingService->get($id);

        if (!$product) {
            return $this->json("Product with id $id not found", Response::HTTP_NOT_FOUND);
        }

        return $this->json($product, Response::HTTP_OK, [], ['groups' => ['read']]);
    }

    #[Route('/api/products', methods: ['POST'])]
    public function createProduct(Request               $request, FormFactoryInterface $formFactoryInterface,
                                  ProductRepository     $productRepository,
                                  CategoryRepository    $categoryRepository,
                                  ProductCachingService $productCachingService): Response
    {

        $form = $this->buildForm($formFactoryInterface, ProductType::class);

        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->respond($form, Response::HTTP_CONFLICT);
        }

        /** @var Product $product */
        $product = $form->getData();

        if ($existingCategory = $categoryRepository->findOneBy(['name' => $product->getCategory()->getName()])) {
            $product->setCategory($existingCategory);
        }

        $productRepository->save($product, true);

        $id = $product->getId();

        $productNormalized = $productCachingService->get($id);

        //@TODO Add document to Elasticsearch (POST with json body)

        return $this->respond($productNormalized, Response::HTTP_CREATED);
    }

    #[Route('/api/products/{id<\d+>}', methods: ['PATCH'])]
    public function updateProduct(Request $request): Response
    {
        $id = $request->get('id');

        $product = $this->productRepository->findOne($id);

        if (!$product) {
            return $this->json("Product with id $id not found", Response::HTTP_NOT_FOUND);
        }

        //@TODO update data in database, after successful update, cache data update elasticsearch

        //@TODO caching product can be done here, after product was saved into database

        //@TODO Update document in Elasticsearch

        return $this->json($product, Response::HTTP_OK);
    }

    #[Route('/api/products/{id<\d+>}', methods: ['DELETE'])]
    public function deleteProduct(Request $request, ProductCacheInvalidator $productCacheInvalidator): Response
    {

        $id = $request->get('id');

        $this->productRepository->remove($this->productRepository->findOne($id), true);

        /** Invalidate cache for product based on tag */
        $productCacheInvalidator->removeProductCache($request->get('id'));

        //@TODO remove document from Elasticsearch (DELETE /<product_index>/_doc/<id>)

        /** Return 204 if successfully deleted*/
        /** Return 404 user calls delete on previously deleted product */
        return $this->json('', Response::HTTP_NO_CONTENT);
    }
}