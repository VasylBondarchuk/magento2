<?php

namespace Tsg\Improvements\Console;

use Magento\Catalog\Api\Data\ProductInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Tsg\Improvements\Configs;

class Sayhello extends Command
{

    const NAME = 'sku';

    private $productRepository;
    private $scopeConfig;

    public function __construct(
        ProductRepository $productRepository,
        ScopeConfigInterface $scopeConfig,
        $skuInput=" ")
    {
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($skuInput);
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

        parent::configure();
    }

    private function isModuleEnabled(): bool
    {
        return  $this->scopeConfig->getValue(Configs::TSG_IMPROVEMENTS_ENABLE_PATH,ScopeInterface::SCOPE_STORE);
    }

    private function getProductBySku(?string $skuIput): ?ProductInterface
    {
        try {
            $product = $skuIput ? $this->productRepository->get($skuIput) : null;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $product = null;
        }

        return $product;
    }

    protected function execute(InputInterface $input,OutputInterface $output)
    {
        $skuIput = $input->getOption(self::NAME);

        $productBySku = $this->getProductBySku($skuIput);

        if(!$this->isModuleEnabled()) {
            $output->writeln("You have not enabled a module!");
            return $this;
        }

        if(!$skuIput) {
            $output->writeln("You have not entered any value!");
            return $this;
        }

        if(!$productBySku && $skuIput) {
            $output->writeln("Entered SKU value is not valid");
            return $this;
        }

        $output->writeln(
                "ID = " . $productBySku->getEntityId() . "\r\n" .
                "Name = " . $productBySku->getName() . "\r\n" .
                "Price = " . $productBySku->getPrice());

        return $this;
    }
}