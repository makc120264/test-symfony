<?php

namespace App\Command;

use App\Service\CategoryService;
use App\Service\ProductService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:update-product')]
class UpdateProductCommand extends Command
{
    /**
     * @var CategoryService
     */
    private CategoryService $categoryService;
    /**
     * @var ProductService
     */
    private ProductService $productService;

    /**
     * @param CategoryService $categoryService
     * @param ProductService $productService
     * @param string|null $name
     */
    public function __construct(
        CategoryService $categoryService,
        ProductService  $productService,
        string          $name = null
    )
    {
        parent::__construct($name);
        $this->categoryService = $categoryService;
        $this->productService = $productService;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = Command::SUCCESS;

        try {
            $output->writeln([
                'Started checking categories',
                '============'
            ]);

            $resultCheck = $this->categoryService->checkCategories();
            foreach ($resultCheck as $value) {
                if (!empty($value)) {
                    $output->writeln([
                        'Warning:',
                        $value
                    ]);
                }
            }

            $output->writeln([
                'Categories check is completed',
                '============',
                'Started checking products'
            ]);
        } catch (\Exception $exception) {
            $output->writeln([
                'Error checking categories',
                $exception->getMessage()
            ]);
            $result = Command::FAILURE;
        }

        try {
            if ($result === Command::SUCCESS) {
                $resultCheck = $this->productService->checkProducts();
                foreach ($resultCheck as $value) {
                    if (!empty($value)) {
                        $output->writeln([
                            'Warning:',
                            $value
                        ]);
                    }
                }

                $output->writeln([
                    'Products check is completed',
                    '============'
                ]);
            }
        } catch (\Exception $exception) {
            $output->writeln([
                'Error checking products',
                $exception->getMessage()
            ]);
            $result = Command::FAILURE;
        }

        return $result;
    }
}
