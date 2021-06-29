<?php

namespace Tsg\Improvements\Ui\Component\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\UrlInterface;
use Tsg\Improvements\Configs;

class Grid extends DataProvider
{
    private $fileScanner;

    public function __construct(
        $name,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        UrlInterface $url,
        FileScanner $fileScanner

    ) {
        $primaryFieldName = 'id';
        $requestFieldName = 'id';
        $meta = [];
        $updateUrl = $url->getUrl('mui/index/render');
        $data = [
            'config' => [
                'component' => 'Magento_Ui/js/grid/provider',
                'update_url' => $updateUrl
            ]
        ];

        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request,
            $filterBuilder, $meta, $data);

        $this->fileScanner = $fileScanner;
    }

    public function getFilesDetailsArray(string $directoryPath):array
    {
        $filesDetailsArray = [];

        foreach($this->fileScanner->getFilesInDirectory($directoryPath) as $fileName)
        {
            $filesDetailsArray[] = array(
                'file_name' => $fileName,
                'file_size' => $this->fileScanner->getFileSize($directoryPath.(Configs::DS).$fileName),
                'modified_at' => $this->fileScanner->getModificationTime($directoryPath.(Configs::DS).$fileName)
            );
        }
        return $filesDetailsArray;
    }

    public function getData()
    {
        $result = [
            'items' => $this->getFilesDetailsArray(Configs::LOG_DIR_PATH),
            'totalRecords' => $this->fileScanner->getFilesNumber(Configs::LOG_DIR_PATH)
        ];
        return $result;
    }
}