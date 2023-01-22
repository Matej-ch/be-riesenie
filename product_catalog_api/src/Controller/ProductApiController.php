<?php

namespace App\Controller;


use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ProductCacheInvalidator;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductApiController extends ApiController
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

        //@TODO: here we should search product inside elasticSearch, instead only simple search on name, price an category name is implemented

        $queryBuilder = $this->productRepository->getPaginator($request->query->all());
        $adapter = new QueryAdapter($queryBuilder);
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage($adapter, $request->query->get('page', 1), $this->productRepository::PRODUCTS_PER_PAGE);

        return $this->json($pager, Response::HTTP_OK, [], ['groups' => ['read']]);
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
            return $this->json("Product with id $id not found", Response::HTTP_NOT_FOUND);
        }

        return $this->json($product, Response::HTTP_OK, [], ['groups' => ['read']]);
    }

    #[Route('/api/products', methods: ['POST'])]
    public function createProduct(Request $request, FormFactoryInterface $formFactoryInterface, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
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

        //@TODO caching product can be done here, after product was saved into database

        return $this->respond('Product was saved', Response::HTTP_CREATED);
    }

    #[Route('/api/products/{id<\d+>}', methods: ['PATCH'])]
    public function updateProduct(Request $request): Response
    {
        $id = $request->get('id');

        $product = $this->productRepository->findOne($id);

        if (!$product) {
            return $this->json("Product with id $id not found", Response::HTTP_NOT_FOUND);
        }

        $product->setName($request->get('name'));
        $product->setPrice($request->get('price'));
        $this->productRepository->save($product, true);

        return $this->json($product, Response::HTTP_OK);
    }

    #[Route('/api/products/{id<\d+>}', methods: ['DELETE'])]
    public function deleteProduct(Request $request, ProductCacheInvalidator $productCacheInvalidator): Response
    {
        //@TODO here should be implemented delete request

        /** Invalidate cache for product based on tag */
        $productCacheInvalidator->removeProductCache($request->get('id'));

        /** Return 204 if successfully deleted*/
        /** Return 404 user calls delete on previously deleted product */
        return $this->json('Product deleted', Response::HTTP_NO_CONTENT);
    }
}