<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 */

namespace Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column\Renderer;

class SrNo extends \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column\DefaultRenderer
{
    /**
     * Returns the serial number for the PDF row
     *
     * @return string
     */
    public function getRowValue()
    {
        $item = $this->getItem();
        $element = $this->getItemRenderer()->getElementModel();
        
        $allItems = $element->getAllItems();
        $srNo = 1;
        
        foreach ($allItems as $tmpItem) {
            if ($tmpItem->getId() == $item->getId()) {
                break;
            }
            
            if (!$element->getOrderItem($tmpItem)->getParentItem()) {
                $srNo++;
            }
        }
        
        return (string)$srNo;
    }
}
