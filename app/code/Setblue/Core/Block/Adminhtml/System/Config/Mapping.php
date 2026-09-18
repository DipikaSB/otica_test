<?php
namespace Setblue\Core\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Mapping extends AbstractFieldArray
{
    /**
     * Prepare columns for Country Code → SEO Text mapping
     */
    protected function _prepareToRender()
    {
        $this->addColumn('field_one', [
            'label' => __('Country Codes'),
            'class' => 'required-entry'
        ]);

        $this->addColumn('field_two', [
            'label' => __('SEO Text'),
            'class' => 'required-entry'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
