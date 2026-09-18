<?php

namespace SEO\Index\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Row extends AbstractFieldArray
{
    /**
     * @var Templete
     */
    private $templeteRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('noindex_nofollow', ['label' => __('Add Noindex / Nofollow Pages'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Row');
    }
}