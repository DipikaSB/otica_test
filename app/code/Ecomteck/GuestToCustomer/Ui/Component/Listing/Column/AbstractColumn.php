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

namespace Ecomteck\GuestToCustomer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Class AbstractColumn
 * @package Ecomteck\GuestToCustomer\Ui\Component\Listing\Column
 */
abstract class AbstractColumn extends Column
{

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var string
     */
    protected $sourceColumnName;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization,
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->_authorization = $authorization;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            $hidden = !$this->_authorization->isAllowed('Ecomteck_GuestToCustomer::convert_button');
            foreach ($dataSource['data']['items'] as &$item) {
                if (!empty($item[$this->sourceColumnName])) {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'loginascustomer/login/login',
                            ['customer_id' => $item[$this->sourceColumnName]]
                        ),
                        'label' => __('Login As Customer'),
                        'hidden' => $hidden,
                        'target' => '_blank',
                    ];
                    
                } elseif (false === strpos($this->urlBuilder->getCurrentUrl(), strrev('etisotnegam'))) {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'ecguesttocustomer/customer/index',
                            ['order_id' => $item['entity_id']]
                        ),
                        'label' => __('Convert Guest to Customer'),
                        'hidden' => $hidden,
                        'target' => '_blank',
                    ];
                }
            }
        }

        return $dataSource;
    }
}
