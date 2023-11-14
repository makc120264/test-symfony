<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryService
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @param CategoryRepository $categoryRepository
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    /**
     * @return array
     */
    public function checkCategories(): array
    {
        $messages = [];
        $categoriesJSON = file_get_contents($_SERVER["PWD"] . '/' . $_ENV["DOWNLOADS_DIR"] . '/categories.json');
        $categories = json_decode($categoriesJSON, true);
        foreach ($categories as $categoryItem) {
            $category = $this->categoryRepository->findOneByField('title', $categoryItem['title']);
            if (is_null($category)) {
                $category = new Category();
                $message = $this->setCategoryData($category, $categoryItem);
                if (empty($message)) {
                    $this->entityManager->persist($category);
                    $this->entityManager->flush();
                } else {
                    $messages[] = $message;
                }
            } else {
                $message = $this->setCategoryData($category, $categoryItem);
                if (empty($message)) {
                    $this->entityManager->flush();
                } else {
                    $messages[] = $message;
                }
            }
        }

        return $messages;
    }

    /**
     * @param $category
     * @param $categoryItem
     * @return string
     */
    private function setCategoryData($category, $categoryItem): string
    {
        $constraintsLengthRule = $this->getConstraintsLengthRule();
        $message = $this->validator->validate($categoryItem["title"], $constraintsLengthRule)->__toString();
        if (empty($message)) {
            $category->setTitle($categoryItem['title']);
            if (!empty($categoryItem["eId"])) {
                $category->setEid($categoryItem["eId"]);
            }
        }

        return $message;
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
                'minMessage' => 'Category title must be at least {{ limit }} characters long',
                'maxMessage' => 'Category title cannot be longer than {{ limit }} characters long',
            ]
        );
    }

}
