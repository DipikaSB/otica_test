<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */

namespace Setblue\Magicslider\Model;

class Magicslider extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = 'magicslider_id';

    /**
     * @var \Setblue\Magicslider\Model\ResourceModel\Magicslider\CollectionFactory
     */
    protected $_magicsliderCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Setblue\Magicslider\Model\ResourceModel\Magicslider\CollectionFactory $magicsliderCollectionFactory,
        \Setblue\Magicslider\Model\ResourceModel\Magicslider $resource,
        \Setblue\Magicslider\Model\ResourceModel\Magicslider\Collection $resourceCollection
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_magicsliderCollectionFactory = $magicsliderCollectionFactory;
    }

}
