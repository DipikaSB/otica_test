<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */


namespace Setblue\Magicslider\Block\Adminhtml\Magicslider\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * construct.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('magicslider_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Magicslider Information'));
    }

}
