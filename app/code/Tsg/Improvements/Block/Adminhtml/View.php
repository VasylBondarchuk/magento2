<?php

declare(strict_types = 1);

namespace Tsg\Improvements\Block\Adminhtml;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Tsg\Improvements\Configs;

class View extends Template
{
    private $file;
    private $urlInterface;

    public function __construct(Context $context, File $file, UrlInterface $urlInterface)
    {
        $this->file = $file;
        $this->urlInterface = $urlInterface;
        parent::__construct($context);
    }

    public function getCurrentPageUrl()
    {
        return $this->urlInterface->getCurrentUrl();
    }

    public function getLastLinesQty(): int
    {
        $urlArray = explode("/",$this->getCurrentPageUrl());
        $qty = (int)$urlArray[count($urlArray)-1];
        return (is_numeric($qty) && $qty > 0) ? $qty : Configs::DEFAULT_LINES_QTY;
    }

    public function getFileName(): string
    {
        $urlArray = explode("/",$this->getCurrentPageUrl());
        return $urlArray[7];
    }

    public function getFilePath(): string
    {
        return Configs::LOG_DIR_PATH . DS . $this->getFileName();
    }

    public function getFileContent(): string
    {
        return $this->file->isReadable($this->getFilePath()) ?
            $this->file->fileGetContents($this->getFilePath()) : " ";
    }

    public function displayFileContent(): string
    {
        $fileContentArray = explode("\n", $this->getFileContent());
        return implode("<br>", array_slice($fileContentArray, -($this->getLastLinesQty() + 1)));
    }

    public function displayGoBackLink(string $path, string $text): string
    {
        return '<a href="' . $this->getUrl($path) . '">' . __($text) . '</a>';
    }
}