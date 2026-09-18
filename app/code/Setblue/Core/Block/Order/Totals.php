<?php

namespace Setblue\Core\Block\Order;

class Totals extends \Magento\Sales\Block\Order\Totals
{
    protected function _initTotals()
    {
        parent::_initTotals();

        // Remove only the "Grand Total to be Charged" if it exists
        unset($this->_totals['base_grandtotal']);

        return $this;
    }
}
