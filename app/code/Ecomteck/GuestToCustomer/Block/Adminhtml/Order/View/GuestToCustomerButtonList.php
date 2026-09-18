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

namespace Ecomteck\GuestToCustomer\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ItemFactory;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Ecomteck\GuestToCustomer\Helper\Data;
use Magento\Framework\AuthorizationInterface;

/**
 * Class GuestToCustomerButtonList
 * @package Ecomteck\GuestToCustomer\Block\Adminhtml\Order\View
 */
class GuestToCustomerButtonList extends ButtonList
{
    /**
     * GuestToCustomerButtonList constructor.
     * @param ItemFactory $itemFactory
     * @param Registry $coreRegistry
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization
     * @param Data $helperData
     */
    public function __construct(
        ItemFactory $itemFactory,
        Registry $coreRegistry,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        Data $helperData
    ) {
        parent::__construct($itemFactory);
        $this->_authorization = $authorization;
        /** @var OrderInterface $order */
        $order = $coreRegistry->registry('current_order');
        $allow_show_button = $this->_authorization->isAllowed('Ecomteck_GuestToCustomer::convert_button');
        if ($helperData->isEnabled() && $order && !$order->getCustomerId() && $allow_show_button) {
            $message ='Are you sure you want to do this?';
            $url = $urlBuilder->getUrl('ecguesttocustomer/customer/index');

            $this->add('ecguesttocustomer', [
                'label' => __('Convert to Customer'),
                'onclick' => "gustToCustomerButtonClick('{$url}', '{$order->getId()}', '{$message}')",
                'id' => 'gustToCustomerButtonClick'
            ]);
        }
    }
}
