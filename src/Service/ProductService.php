<?php

namespace App\Service;

use App\Entity\Product;
use App\Event\ProductCreatedEvent;
use App\Event\ProductEventSubscriber;
use App\Event\ProductUpdatedEvent;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return array
     * @throws NonUniqueResultException
     */
    public function checkProducts(): array
    {
        $messages = [];
        $productsJSON = file_get_contents($_SERVER["PWD"] . '/' . $_ENV["DOWNLOADS_DIR"] . '/products.json');
        $products = json_decode($productsJSON, true);
        foreach ($products as $productItem) {
            $product = $this->productRepository->findOneByField('title', $productItem['title']);
            if (is_null($product)) {
                $product = new Product();
                $message = $this->setProductData($product, $productItem);
                if (empty($message)) {
                    $this->addNewProduct($product);
                    $this->addProductCreatedEvent($product);
                } else {
                    $messages[] = $message;
                }
            } else {
                $product->getCategories()->initialize();
                $message = $this->setProductData($product, $productItem);
                if (empty($message)) {
                    $this->updateProduct();
                    $this->addProductUpdatedEvent($product);
                } else {
                    $messages[] = $message;
                }
            }
        }

        return $messages;
    }

    /**
     * @param $product
     * @return void
     */
    private function addProductUpdatedEvent($product): void
    {
        $event = new ProductUpdatedEvent($product);
        $this->eventDispatcher->addSubscriber(new ProductEventSubscriber());
        $this->eventDispatcher->dispatch($event, ProductUpdatedEvent::NAME);
    }

    /**
     * @param $product
     * @return void
     */
    private function addProductCreatedEvent($product): void
    {
        $event = new ProductCreatedEvent($product);
        $this->eventDispatcher->addSubscriber(new ProductEventSubscriber());
        $this->eventDispatcher->dispatch($event, ProductCreatedEvent::NAME);
    }

    /**
     * @param $product
     * @param $productItem
     * @return string
     * @throws NonUniqueResultException
     */
    private function setProductData($product, $productItem): string
    {
        $result = '';
        $constraintsLengthRule = $this->getConstraintsLengthRule();
        $message = $this->validator->validate($productItem["title"], $constraintsLengthRule)->__toString();
        if (empty($message)) {
            $product->setTitle($productItem['title']);

            $constraintsPriceRule = $this->getConstraintsRangeRule();
            $message = $this->validator->validate($productItem["price"], $constraintsPriceRule)->__toString();
            if (empty($message)) {
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
            } else {
                $result = $message;
            }
        } else {
            $result = $message;
        }

        return $result;
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

    /**
     * @return Assert\Range
     */
    private function getConstraintsRangeRule(): Assert\Range
    {
        return new Assert\Range([
            'min' => 0,
            'max' => 200,
            'notInRangeMessage' => 'Price must be between {{ min }} and {{ max }}.',
        ]);
    }

    /**
     * @return Assert\Length
     */
    private function getConstraintsLengthRule(): Assert\Length
    {
        return new Assert\Length(
            [
                'min' => 3,
                'max' => 12,
                'minMessage' => 'Product title must be at least {{ limit }} characters long',
                'maxMessage' => 'Product title cannot be longer than {{ limit }} characters long',
            ]
        );
    }
}
