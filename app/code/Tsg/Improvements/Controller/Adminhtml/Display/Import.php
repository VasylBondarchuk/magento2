<?php

declare(strict_types = 1);

namespace Tsg\Improvements\Controller\Adminhtml\Display;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Io\Ftp;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Tsg\Improvements\CsvValidator;
use Tsg\Improvements\FtpConnection;
use Tsg\Improvements\Model\ImportCsvConfigs;
use Psr\Log\LoggerInterface;
use Tsg\Improvements\CsvCreationFailureEmail;

class Import extends \Magento\Backend\App\Action
{
    private $resultPageFactory = false;
    private $ftpConn;
    private $ftp;
    private $sourceItemsSave;
    private $sourceItemFactory;
    private $csvValidator;
    private $configs;
    private $logger;
    private $csvCreationFailureEmail;

    public function __construct(
        PageFactory $resultPageFactory,
        Context $context,
        Ftp $ftp,
        FtpConnection $ftpConnection,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory,
        CsvValidator $csvValidator,
        ImportCsvConfigs $configs,
        LoggerInterface $logger,
        CsvCreationFailureEmail $csvCreationFailureEmail
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ftp = $ftp;
        $this->ftpConn= $ftpConnection;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory  = $sourceItemFactory;
        $this->csvValidator = $csvValidator;
        $this->configs = $configs;
        $this->logger = $logger;
        $this->csvCreationFailureEmail = $csvCreationFailureEmail;

        parent::__construct($context);
    }

    public function importCsvFileFromFtp()
    {
        $ftpFilePath = $this->configs->getFtpImportPath();
        $localCsvFilePath = $this->configs->getLocalImportPath();

        // make a connection
        try {
            if (!$this->ftpConn->isConnSuccessful()) {
                $this->ftpConn->sendFtpConnFailureEmail();
                return;
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        try {
            $this->ftp->read($ftpFilePath, $localCsvFilePath);
            $this->ftp->close();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            $this->csvCreationFailureEmail->sendCsvCreationFailureEmail($e->getMessage());
        }

        return $localCsvFilePath;
    }

    public function setQtyToProduct($sku, $qty, $source)
    {
        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSourceCode($source);
        $sourceItem->setSku(trim($sku));
        $sourceItem->setQuantity($qty);
        $sourceItem->setStatus((int)($qty > 0));

        $this->sourceItemsSave->execute([$sourceItem]);
    }

    public function sendCsvDataToDb(array $csvData)
    {
        if ($csvData) {
            foreach ($csvData as $row) {
                $this->setQtyToProduct($row["Sku"], (float)$row["Qty"], $row["Source"]);
            }
        }
    }

    public function execute()
    {
        $this->importCsvFileFromFtp();
        $this->sendCsvDataToDb($this->csvValidator->getValidatedCsvData($this->configs->getLocalImportPath()));

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Import from FTP'));

        return $resultPage;
    }
}
