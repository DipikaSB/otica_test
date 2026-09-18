<?php
/**
 * @copyright Copyright (c) 2015 Setblue Limited (http://www.setblue.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Setblue\PrintOrderPdf\Model\Pdf;

class BundleItems extends \Magento\Bundle\Model\Sales\Order\Pdf\Items\Invoice
{
    public function getChildren($orderItem)
    {

        $itemsArray = [];
        $items = $orderItem->getOrder()->getAllItems();

        if ($items) {
            foreach ($items as $item) {
                $parentItem = $item->getParentItem();
                $item->setQty($item->getQtyOrdered());
                $item->setOrderItem($item);
                if ($parentItem) {
                    $itemsArray[$parentItem->getId()][$item->getId()] = $item;
                } else {
                    $itemsArray[$item->getId()][$item->getId()] = $item;
                }
            }
        }

        return isset($itemsArray[$orderItem->getId()]) ? $itemsArray[$orderItem->getId()] : null;
    }
}
