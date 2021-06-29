<?php

namespace Tsg\Improvements\Ui\Component\DataProvider;

use Tsg\Improvements\Configs;

class FileScanner
{
    public function getFilesInDirectory(string $directoryPath):array
    {
        $fileNames = [];
        $content = scandir($directoryPath);

        foreach($content as $item)
        {
            if(is_file($directoryPath.Configs::DS.$item)) {
                $fileNames[] = $item;
            }
        }
        return $fileNames;
    }

    public function getFileSize(string $filePath): string
    {
        $result = "";
        $bytes = floatval(filesize($filePath));

        $arBytes = array(
            0 => array("UNIT" => "TB","VALUE" => pow(1024, 4)),
            1 => array("UNIT" => "GB","VALUE" => pow(1024, 3)),
            2 => array("UNIT" => "MB","VALUE" => pow(1024, 2)),
            3 => array("UNIT" => "KB","VALUE" => 1024),
            4 => array("UNIT" => "B","VALUE" => 1)
        );

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
        return date("l, dS F, Y, h:ia", filemtime($filePath));
    }

    public function getFilesNumber(string $directoryPath):int
    {
        return count($this->getFilesInDirectory($directoryPath));
    }

}