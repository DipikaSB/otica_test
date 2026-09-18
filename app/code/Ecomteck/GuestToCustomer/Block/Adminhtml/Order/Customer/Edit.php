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

namespace Ecomteck\GuestToCustomer\Block\Adminhtml\Order\Customer;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;
use Ecomteck\GuestToCustomer\Helper\Data;

/**
 * Class Edit
 * @package Ecomteck\GuestToCustomer\Block\Adminhtml\Order\Customer
 */
class Edit extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * Ecomteck Helper
     *
     * @var Data
     */
    protected $_helper;

    /**
     * @var Context $context
     * @var Registry $coreRegistry
     * @var AuthorizationInterface
     * @var Data $helper
     */
    protected $authorization;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param AuthorizationInterface $authorization
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AuthorizationInterface $authorization,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
        $this->authorization = $authorization;
        $this->_helper = $helper;
    }

    /**
     * @return string
     */
    public function getAdminPostUrl()
    {
        return $this->getUrl('ecguesttocustomer/edit/index');
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    public function getOrder()
    {
        return $this->coreRegistry->registry('sales_order');
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        /** @var OrderInterface $order */
        if ($order = $this->getOrder()) {
            return $order->getCustomerId();
        }

        return '';
    }

    /**
     * @return bool
     */
    public function hasCustomerId()
    {
        /** @var OrderInterface $order */
        if ($order = $this->getOrder()) {
            return $order->getCustomerId() ? true : false;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_authorization->isAllowed('Ecomteck_GuestToCustomer::change_customer')) {
            return '';
        }

        return parent::_toHtml();
    }
}
