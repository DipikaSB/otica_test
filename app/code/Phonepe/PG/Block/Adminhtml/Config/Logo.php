<?php
namespace Phonepe\PG\Block\Adminhtml\Config;

use Magento\Config\Block\System\Config\Form\Field;

class Logo extends Field
{
    protected $_template = 'Phonepe_PG::config/logo.phtml';
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
