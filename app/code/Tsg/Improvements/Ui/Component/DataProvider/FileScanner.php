<?php

declare(strict_types = 1);

namespace Tsg\Improvements\Ui\Component\DataProvider;

use Magento\Framework\Filesystem\Driver\File;

class FileScanner
{
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function getFileName(string $filePath):string
    {
       $filePathArray = explode(DS,$filePath);
       return $filePathArray[count($filePathArray)-1];
    }

    public function getFilesInDirectory(string $directoryPath):array
    {
        $fileNames = [];
        $content = $this->file->readDirectory($directoryPath);

        foreach($content as $item)
        {
            if($this->file->isFile($item)){
                $fileNames[] = $this->getFileName($item);
            }
        }
        return $fileNames;
    }

    public function getFileSize(string $filePath): string
    {
        $result = "";
        $bytes = floatval($this->file->stat($filePath)['size']);

        $arBytes = [
            ["UNIT" => "TB","VALUE" => pow(1024, 4)],
            ["UNIT" => "GB","VALUE" => pow(1024, 3)],
            ["UNIT" => "MB","VALUE" => pow(1024, 2)],
            ["UNIT" => "KB","VALUE" => 1024],
            ["UNIT" => "B","VALUE" => 1]
        ];

        foreach($arBytes as $arItem)
        {
            if($bytes >= $arItem["VALUE"])
            {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result ? $result : "0 B" ;
    }

    public function getModificationTime($filePath): string
    {
        return date("l, dS F, Y, h:ia", $this->file->stat($filePath)['mtime']);
    }

    public function getFilesNumber(string $directoryPath):int
    {
        return count($this->getFilesInDirectory($directoryPath));
    }
}