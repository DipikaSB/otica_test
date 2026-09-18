<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Block\TagManager;

class CatLandingPage extends Manager
{
    /**
     * GetProductCollection
     */
    public function getProductCollection()
    {
        $categoryProductListBlock = $this->_layout->getBlock('category.products.list');

        if (empty($categoryProductListBlock)) {
            return [];
        }

        $categoryProductListBlock->toHtml();
        $collection = $categoryProductListBlock->getLoadedProductCollection();
        $collection->setCurPage($this->getCurrentPage())->setPageSize($this->getLimit());

        return $collection;
    }

    /**
     * GetLimit
     */
    protected function getLimit()
    {
        $productListBlockToolbar = $this->_layout->getBlock('product_list_toolbar');
        if (empty($productListBlockToolbar)) {
            return 9;
        }

        return (int) $productListBlockToolbar->getLimit();
    }

    /**
     * GetCurrentPage
     */
    protected function getCurrentPage()
    {
        $page = (int) $this->_request->getParam('p');
        if (!$page) {
            return 1;
        }

        return $page;
    }
}
