<?php

namespace Setblue\Core\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Zend_Pdf_Exception;
use finfo;
use Exception;

class ImageFactory extends \Magento\Framework\File\Pdf\ImageResource\ImageFactory
{
    /**
     * Fix interlaced PNG issue for Zend_Pdf
     */
    protected function getZendPdfImage(string $typeOfImage, string $tempResourceFilePath)
    {
        // Fix for interlaced PNG
        if ($typeOfImage === 'png' && extension_loaded('gd')) {
            try {
                $image = @imagecreatefrompng($tempResourceFilePath);
                if ($image !== false) {
                    imageinterlace($image, false);
                    imagepng($image, $tempResourceFilePath);
                    imagedestroy($image);
                }
            } catch (\Exception $e) {
                // Fail silently, Zend will throw if invalid
            }
        }

        $classToUseAsPdfImage = sprintf(
            'Zend_Pdf_Resource_Image_%s',
            ucfirst($typeOfImage)
        );

        return new $classToUseAsPdfImage($tempResourceFilePath);
    }
}
