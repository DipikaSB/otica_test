<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * PHP version 5.3 or later
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
namespace Magetrend\PdfTemplates\Helper;

/**
 * Qr code generator
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class QRcode
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $curl;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    public $io;

    /**
     * QRcode constructor.
     *
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Filesystem\Io\File $io
     */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Filesystem\Io\File $io
    ) {
        $this->curl = $curl;
        $this->io = $io;
    }

    /**
     * Generate qr code
     *
     * @param $data
     * @param $width
     * @param $height
     * @param bool $pathToFile
     */
    public function png($data, $width = 350, $height = 350, $pathToFile = false)
    {
        $direcoty = $this->getDirectory($pathToFile);
        $fileName = $this->getFileName($pathToFile);

        if (class_exists('\Endroid\QrCode\Builder\Builder')) {
            return $this->endroid4x($data,  $width, $height, $pathToFile);
        }

        if (class_exists('\Endroid\QrCode\QrCode')) {
            return $this->endroid3x($data,  $width, $height, $pathToFile);
        }

        return false;
    }

    public function endroid4x($data, $width, $height, $pathToFile = false)
    {
        try {
            $result = \Endroid\QrCode\Builder\Builder::create()
                ->writer(new \Endroid\QrCode\Writer\PngWriter())
                ->writerOptions([])
                ->data($data)
                ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
                ->errorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh())
                ->size($width > $height?$width*3:$height * 3)
                ->margin(0)
                ->roundBlockSizeMode(new \Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeNone())
                ->validateResult(false)
                ->build();

            $result->saveToFile($pathToFile);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function endroid3x($data,  $width, $height, $pathToFile = false)
    {
        try {
            $qrCode = new \Endroid\QrCode\QrCode($data);
            $qrCode->setSize($width > $height?$width*3:$height * 3);
            $qrCode->setMargin(0);

            $qrCode->setWriterByName('png');
            $qrCode->setEncoding('UTF-8');
            $qrCode->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::HIGH());
            $qrCode->setRoundBlockSize(false); // The size of the qr code is shrinked, if necessary, but the size of the final image remains unchanged due to additional margin being added (default)
            $qrCode->setWriterOptions(['exclude_xml_declaration' => true]);
            $qrCode->setValidateResult(false);

            $qrCode->writeFile($pathToFile);

            return $pathToFile;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Removes file name and returns directory
     *
     * @param $pathToFile
     * @return string
     */
    public function getDirectory($pathToFile)
    {
        $directory = explode('/', $pathToFile);
        array_pop($directory);
        $directory = implode('/', $directory);
        return $directory;
    }

    /**
     * Returns file name from path to file
     *
     * @param $pathToFile
     * @return string
     */
    public function getFileName($pathToFile)
    {
        $pathToFile = explode('/', $pathToFile);
        return end($pathToFile);
    }
}
