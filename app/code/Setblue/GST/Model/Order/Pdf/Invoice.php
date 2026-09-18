<?php

namespace Setblue\GST\Model\Order\Pdf;

use Magento\Sales\Model\Order\Pdf\Invoice as CoreInvoice;
use Zend_Pdf_Color_Rgb;
use Zend_Pdf;

class Invoice extends CoreInvoice
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice[] $invoices
     * @return Zend_Pdf
     */
    public function getPdf($invoices = [])
    {
        $pdf = parent::getPdf($invoices);

        foreach ($invoices as $invoice) {

            $gstNumber = $invoice->getData('gst_number');

            if (!$gstNumber) {
                continue;
            }

            $page = $pdf->pages[count($pdf->pages) - 1];

            // White color (#FFF)
            $page->setFillColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
            $this->_setFontBold($page, 10);

            $text = __('GST Number: ') . $gstNumber;

            // Move to right side (adjust X if needed)
            $xPosition = 390; 
            $yPosition = 755;

            $page->drawText(
                $text,
                $xPosition,
                $yPosition,
                'UTF-8'
            );
        }

        return $pdf;
    }
}
