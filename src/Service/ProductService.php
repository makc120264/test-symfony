<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

class ProductService
{
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    /**
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return void
     * @throws NonUniqueResultException
     */
    public function checkProducts(): void
    {
        $productsJSON = file_get_contents($_SERVER["PWD"] . '/' . $_ENV["DOWNLOADS_DIR"] . '/products.json');
        $products = json_decode($productsJSON, true);
        foreach ($products as $productItem) {
            $product = $this->productRepository->findOneByField('title', $productItem['title']);
            if (is_null($product)) {
                $product = new Product();
                $this->setProductData($product, $productItem);
                $this->addNewProduct($product);
            } else {
                $product->getCategories()->initialize();
                $this->setProductData($product, $productItem);
                $this->updateProduct();
            }
        }
    }

    /**
     * @param $product
     * @param $productItem
     * @return void
     * @throws NonUniqueResultException
     */
    private function setProductData($product, $productItem): void
    {
        // todo add validate title and price
        $product->setTitle($productItem['title']);
        $product->setPrice($productItem["price"]);

        if (!empty($productItem["eId"])) {
            $product->setEid($productItem["eId"]);
        }

        $productCategoryIds = $this->getProductCategoryIds($product);
        if (!empty($productItem["categoriesEId"])) {
            $categoriesEId = $productItem["categoriesEId"];
        }
        if (!empty($productItem["categoryEId"])) {
            $categoriesEId = $productItem["categoryEId"];
        }

        if (!empty($categoriesEId)) {
            foreach ($categoriesEId as $productCategoryEId) {
                $category = $this->categoryRepository->findOneByField('eid', $productCategoryEId);
                if (!is_null($category) && !in_array($category->getId(), $productCategoryIds)) {
                    $product->addCategory($category);
                }
            }
        }
    }

    /**
     * @param $product
     * @return array
     */
    private function getProductCategoryIds($product): array
    {
        $productCategoryIds = [];
        $productCategories = $product->getCategories()->getValues();
        foreach ($productCategories as $productCategory) {
            $productCategoryIds[] = $productCategory->getId();
        }
        return $productCategoryIds;
    }

    /**
     * @return void
     */
    public function updateProduct(): void
    {
        $this->entityManager->flush();
    }

    /**
     * @param $product
     * @return void
     */
    public function removeProduct($product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }

    /**
     * @param $product
     * @return void
     */
    public function addNewProduct($product): void
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }
}
