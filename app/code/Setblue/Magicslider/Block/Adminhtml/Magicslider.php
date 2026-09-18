<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */

namespace Setblue\Magicslider\Block\Adminhtml;

class Magicslider extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor.
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_magicslider';
        $this->_blockGroup = 'Setblue_Magicslider';
        $this->_headerText = __('Magicslider');
        $this->_addButtonLabel = __('Add New Magicslider');
        parent::_construct();
    }
}
