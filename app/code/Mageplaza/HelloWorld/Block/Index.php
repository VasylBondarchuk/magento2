<?php
namespace Mageplaza\HelloWorld\Block;

use Magento\Framework\View\Element\Template;

class Index extends Template
{
    public function getFilesInDirectory(string $dir):array
    {
        return array_diff(scandir($dir), array('.', '..'));
    }

    public function getModificationTime(string $file): string
    {
        return date("l, dS F, Y, h:ia", filemtime($file));
    }

    public function getFileSize(string $file): string
    {

        $result = "";

        $bytes = floatval(filesize($file));

        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
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

}