<?php
namespace Setblue\OrderGrid\Model\ResourceModel\Order\Grid;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{
    protected function _renderFiltersBefore()
    {
        // 1. Join address to get country_id (original code)
        $this->getSelect()->joinLeft(
            ["soa" => "sales_order_address"],
            "main_table.entity_id = soa.parent_id and soa.address_type = 'shipping'",
            array('country_id')
        );

        // 2. Join sales_order_item table to fetch SKUs. 
        // We filter for "parent_item_id IS NULL" so that we only target main items (avoiding duplicates from child items in configurable/bundle products).
        $this->getSelect()->joinLeft(
            ["soi" => "sales_order_item"],
            "main_table.entity_id = soi.order_id AND soi.parent_item_id IS NULL",
            array('skus' => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT soi.sku SEPARATOR ", ")'))
        );

        // 3. Group by the main table's entity_id to ensure orders with multiple items do not create duplicate rows in the grid
        $this->getSelect()->group('main_table.entity_id');

        parent::_renderFiltersBefore();
    }

    protected function _initSelect()
    {
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('base_grand_total', 'main_table.base_grand_total');
        $this->addFilterToMap('grand_total', 'main_table.grand_total');
        $this->addFilterToMap('store_id', 'main_table.store_id');
        $this->addFilterToMap('store_name', 'main_table.store_name');
        $this->addFilterToMap('order_id', 'main_table.order_id');
        $this->addFilterToMap('order_increment_id', 'main_table.order_increment_id');
        $this->addFilterToMap('billing_name', 'main_table.billing_name');
        
        // BUG FIX: Corrected duplicate 'billing_name' mapping from your original code to 'shipping_name'
        $this->addFilterToMap('shipping_name', 'main_table.shipping_name');
        $this->addFilterToMap('status', 'main_table.status');

        // 4. Map the grid column filter 'skus' to the physical column 'soi.sku' so filtering/searching by SKU works correctly
        $this->addFilterToMap('skus', 'soi.sku');

        parent::_initSelect();
    }
}