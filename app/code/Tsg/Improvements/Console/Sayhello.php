<?php

namespace Tsg\Improvements\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Catalog\Model\ProductRepository;
use Tsg\Improvements\Helper\Data;

class Sayhello extends Command
{

    const NAME = 'sku';

    private $productRepository;
    private $helperData;

    public function __construct(ProductRepository $productRepository, Data $helperData, $sku=" ")
    {
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
        parent::__construct($sku);
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                self::NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Name'
            )
        ];

        $this->setName('improvements:get-product-details')
            ->setDescription('Displays product details by SKU')
            ->setDefinition($options);
dd
        parent::configure();
    }

    // Check the module status (0 - disabled, 1 - enabled)

    protected function isModuleEnabled(): bool
    {
        return $this->helperData->getConfig('improvements/general/enable');
    }

    // Get a product by sku, if not valid - return false

    protected function getProductBySku($sku)
    {
        try {
            $product = $sku ? $this->productRepository->get($sku) : null;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $product = null;
        }

        return $product;
    }

    protected function execute(InputInterface $input,OutputInterface $output)
    {
        // Get an input value

        $sku= $input->getOption(self::NAME);

        // Get a product by sku

        $product_by_sku = $this->getProductBySku($sku);

        // A case of disabled method

        if (!$this->isModuleEnabled()) {
            $output->writeln("You have not enabled a module!");
            exit();
        }

        // A case of an empty input or missing input

        if (!$sku) {
            $output->writeln("You have not entered any value!");
        }

        // A case of a not valid nonempty input

        if (!$product_by_sku && $sku) {
            $output->writeln("Entered SKU value is not valid");
        }

        // A case of a nonempty and a valid input

        if ($sku && $product_by_sku) {
            $output->writeln(
                "ID = " . $product_by_sku->getEntityId() . "\r\n" .
                "Name = " . $product_by_sku->getName() . "\r\n" .
                "Price = " . $product_by_sku->getPrice());
        }

        return $this;
    }
}