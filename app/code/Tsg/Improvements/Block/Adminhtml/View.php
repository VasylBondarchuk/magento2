<?php

namespace Tsg\Improvements\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Tsg\Improvements\Configs;

class View extends Template
{
    public function getCurrentPageUrl()
    {
        return $this->getRequest()->getUriString();
    }

    public function getLastLinesQty():int
    {
        // retrieve qty as last section of url
        $qty = basename($this->getCurrentPageUrl());

        return  (is_numeric($qty) && $qty > 0) ? $qty : Configs::DEFAULT_LINES_QTY;
    }

    public function getFileName():string
    {
        $url =  $this->getCurrentPageUrl();
        return  explode("/",parse_url($url, PHP_URL_PATH))[5];
    }

    public function getFilePath():string
    {
        return Configs::LOG_DIR_PATH.DIRECTORY_SEPARATOR.$this->getFileName();
    }

    public function getFileContent(int $lastLinesQty = 10):string
    {
        $fileContentArray = [];

        if(is_readable($this->getFilePath())){
            $handle = fopen($this->getFilePath(), "r");
            if($handle) {
                while(($line = fgets($handle)) !== false)
                {
                    $fileContentArray[] = $line;
                }
                fclose($handle);
            }
        }
        return implode("<br>",array_slice($fileContentArray,-$this->getLastLinesQty()));
    }

    public function displayGoBackLink(string $path, string $text):string
    {
        return '<a href="'.$this->getUrl($path).'">'.__($text).'</a>';
    }
}


