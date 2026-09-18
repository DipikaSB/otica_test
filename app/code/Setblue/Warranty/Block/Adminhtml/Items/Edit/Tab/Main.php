<?php

namespace Setblue\Warranty\Block\Adminhtml\Items\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{
    protected $_wysiwygConfig;
    public $_storeManager;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_storeManager=$storeManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Warranty Information');
    }

    public function getTabTitle()
    {
        return __('Warranty Information');
    }

    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_Setblue_Warranty_items');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('item_');
        // $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Product Information')]);
        $fieldset = $form->addFieldset('customer_fieldset', ['legend' => __('Customer Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'fname',
            'text',
            [
                'name' => 'fname',
                'label' => __('First Name'),
                'title' => __('First Name'),
                'readonly' => 'true',
            ]
        );

        $fieldset->addField(
            'lname',
            'text',
            [
                'name' => 'lname',
                'label' => __('Last Name'),
                'title' => __('Last Name'),
                'readonly' => 'true',
            ]
        );
        
        
        $fieldset->addField(
            'phone_no',
            'text',
            [
                'name' => 'phone_no',
                'label' => __('Mobile/Whatsapp No.'),
                'title' => __('Mobile/Whatsapp No.'),
                'index' => 'customer_name',
                'readonly' => 'true',
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
                'index' => 'customer_name',
                'readonly' => 'true',
            ]
        );

        $fieldset = $form->addFieldset('quote_fieldset', ['legend' => __('Inquiries Information')]);

        $fieldset->addField(
            'product_name',
            'text',
            [
                'name' => 'product_name',
                'label' => __('Product Name'),
                'title' => __('Product Name'),
                'readonly' => 'true',
            ]
        );
        $fieldset->addField(
            'serial_no',
            'text',
            [
                'name' => 'serial_no',
                'label' => __('Serial No'),
                'title' => __('Serial No'),
                'readonly' => 'true',
            ]
        );
        $fieldset->addField(
            'purchase_date',
            'text',
            [
                'name' => 'purchase_date',
                'label' => __('Purchase Date'),
                'title' => __('Purchase Date'),
                'readonly' => 'true',
            ]
        );
        $fieldset->addField(
            'purchase_receipt',
            'text',
            [
                'name' => 'purchase_receipt',
                'label' => __('Purchase Receipt'),
                'title' => __('Purchase Receipt'),
                'readonly' => 'true',
            ]
        );


        $fieldset->addField(
            'created_date',
            'text',
            [
                'name' => 'created_date',
                'label' => __('Created Date'),
                'title' => __('Created Date'),
                'readonly' => 'true',
            ]
        );

        // $fieldset->addField(
        //     'view',
        //     'textarea',
        //     [
        //         'name' => 'view',
        //         'label' => __('View'),
        //         'title' => __('View'),
        //     ]
        // );
     
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
