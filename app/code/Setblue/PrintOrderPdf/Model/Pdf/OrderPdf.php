<?php

namespace Setblue\PrintOrderPdf\Model\Pdf;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class OrderPdf
{
    protected $storeManager;
    protected $productRepository;
    protected $mediaDirectory;

    public function __construct(
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        Filesystem $filesystem
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * Backward compatibility
     */
    public function getPdf($orders = [])
    {
        return $this->generatePdf($orders);
    }

    /**
     * Main PDF generator
     */
    public function generatePdf($orders = [])
    {
        $pdf = new \Zend_Pdf();

        // LANDSCAPE PAGE
        $page = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
        $pdf->pages[] = $page;

        $this->setFontRegular($page, 10);

        // =======================
        // SUMMARY
        // =======================
        $totalOrders = count($orders);
        $totalItems = 0;
        $skuList = [];

        foreach ($orders as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $totalItems += (int)$item->getQtyOrdered();
                $skuList[] = $item->getSku();
            }
        }

        $totalSkus = count(array_unique($skuList));

        // =======================
        // HEADER (SINGLE ROW)
        // =======================
        $yHeader = 560;

        $this->setFontBold($page, 16);
        $page->drawText('Pick List', 35, $yHeader, 'UTF-8');

        $this->setFontRegular($page, 10);
        $summary = "Total Orders: {$totalOrders} | SKUs: {$totalSkus} | Items: {$totalItems}";

        $textWidth = strlen($summary) * 5;
        $pageWidth = $page->getWidth();

        $page->drawText($summary, $pageWidth - $textWidth - 40, $yHeader, 'UTF-8');

        // =======================
        // TABLE HEADER
        // =======================
        $this->setFontBold($page, 10);

        $y = 520;

        $page->drawText('Country', 35, $y);
        $page->drawText('Order ID', 100, $y);
        $page->drawText('Product', 180, $y);
        $page->drawText('SKU', 520, $y);
        $page->drawText('Qty', 650, $y);
        $page->drawText('Status', 700, $y);

        $y -= 25;

        // =======================
        // ROWS
        // =======================
        $this->setFontRegular($page, 10);

        foreach ($orders as $order) {

            $shippingAddress = $order->getShippingAddress();
            $country = $shippingAddress ? $shippingAddress->getCountryId() : '';

            foreach ($order->getAllVisibleItems() as $item) {

                if ($y < 80) {
                    $page = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
                    $pdf->pages[] = $page;

                    $this->setFontRegular($page, 10);
                    $y = 550;
                }

                $productName = $item->getName();
                $sku = $item->getSku();
                $qty = (int)$item->getQtyOrdered();
                $status = $order->getStatus();

                // =======================
                // IMAGE (BIG + BORDER FIXED)
                // =======================
                $imgX1 = 180;
                $imgY1 = $y - 60;
                $imgX2 = 250;
                $imgY2 = $y;

                $imagePath = $this->getProductImagePath($item->getProductId());

                if ($imagePath && file_exists($imagePath)) {
                    try {
                        $image = \Zend_Pdf_Image::imageWithPath($imagePath);
                        $page->drawImage($image, $imgX1, $imgY1, $imgX2, $imgY2);
                    } catch (\Exception $e) {}
                }

                // BORDER (ONLY LINES — NO FILL)
                $page->setLineWidth(0.5);
                $page->drawLine($imgX1, $imgY1, $imgX2, $imgY1);
                $page->drawLine($imgX1, $imgY2, $imgX2, $imgY2);
                $page->drawLine($imgX1, $imgY1, $imgX1, $imgY2);
                $page->drawLine($imgX2, $imgY1, $imgX2, $imgY2);

                // =======================
                // PRODUCT NAME (WRAP)
                // =======================
                $lines = $this->wrapText($productName, 40);
                $textY = $y;

                foreach ($lines as $line) {
                    $page->drawText($line, 260, $textY, 'UTF-8');
                    $textY -= 12;
                }

                // OTHER DATA
                $page->drawText($country, 35, $y, 'UTF-8');
                $page->drawText($order->getIncrementId(), 100, $y, 'UTF-8');
                $page->drawText($sku, 520, $y, 'UTF-8');
                $page->drawText($qty, 650, $y, 'UTF-8');
                $page->drawText($status, 700, $y, 'UTF-8');

                $rowHeight = max(70, count($lines) * 12);
                $y -= $rowHeight;
            }
        }

        return $pdf;
    }

    /**
     * Get product image path
     */
    protected function getProductImagePath($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            $image = $product->getSmallImage();

            if (!$image || $image === 'no_selection') {
                return false;
            }

            return $this->mediaDirectory->getAbsolutePath('catalog/product' . $image);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Wrap text
     */
    protected function wrapText($text, $maxChars = 30)
    {
        return explode("\n", wordwrap($text, $maxChars, "\n", true));
    }

    /**
     * Fonts (BLACK ONLY)
     */
    protected function setFontRegular($page, $size = 10)
    {
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->setFont(
            \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA),
            $size
        );
    }

    protected function setFontBold($page, $size = 12)
    {
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->setFont(
            \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA_BOLD),
            $size
        );
    }
}