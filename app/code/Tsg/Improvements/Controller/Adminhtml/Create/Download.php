<?php

declare(strict_types = 1);

namespace Tsg\Improvements\Controller\Adminhtml\Create;

use Magento\Backend\App\Action\Context;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Filesystem\Driver\File;
use Tsg\Improvements\Configs;

class Download extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    private $urlInterface;
    private $file;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        UrlInterface $urlInterface,
        File $file
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->urlInterface = $urlInterface;
        $this->file = $file;
    }

    public function getFilePathFromUrl()
    {
        $url = $this->urlInterface->getCurrentUrl();
        return (Configs::LOG_DIR_PATH) . DS . basename($url);
    }

    public function getFileNameFromPath(string $filePath):string
    {
        $filePathArray = explode(DS,$filePath);
        return $filePathArray[count($filePathArray)-1];
    }

    public function downloadFile($filePath)
    {
        if ($this->file->isExists($filePath)){
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $this->getFileNameFromPath($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . $this->file->stat($filePath)['size']);
            readfile($filePath);
        }
    }

    public function execute()
    {
        $this->downloadFile($this->getFilePathFromUrl());
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Log File Downloadind'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tsg_Improvements::menu');
    }
}
