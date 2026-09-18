<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_GuestToCustomer
 * @copyright   Copyright (c) 2019 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */

namespace Ecomteck\GuestToCustomer\Block\View\Element\Html\Link;

use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;
use Ecomteck\GuestToCustomer\Helper\Data;

/**
 * Class Current
 * @package Ecomteck\GuestToCustomer\Block\View\Element\Html\Link
 */
class Current extends \Magento\Framework\View\Element\Html\Link\Current
{

    /* @var Data*/
    protected $helperData;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param array $data
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Data $helperData,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->helperData = $helperData;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->helperData->isEnabledCustomerDashboard()) {
            return parent::_toHtml();
        }

        return '';
    }
}
