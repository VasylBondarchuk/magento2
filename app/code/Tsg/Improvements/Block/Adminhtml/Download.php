<?php

namespace Tsg\Improvements\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Tsg\Improvements\Configs;

class Download extends Template{

    public function getFileNameFromUrl()
    {
        $url = $this->getRequest()->getUriString();
        return (Configs::LOG_DIR_PATH).DIRECTORY_SEPARATOR.basename($url);
    }

    public function downloadFile($filePath)
    {
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }
    }
}


