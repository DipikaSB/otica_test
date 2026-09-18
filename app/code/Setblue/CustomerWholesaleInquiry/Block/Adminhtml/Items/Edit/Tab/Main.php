<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_CustomerWholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */
 
namespace Setblue\CustomerWholesaleInquiry\Block\Adminhtml\Items\Edit\Tab;

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
        return __('Enquirys Information');
    }

    public function getTabTitle()
    {
        return __('Enquirys Information');
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
        $model = $this->_coreRegistry->registry('current_Setblue_CustomerWholesaleInquiry_items');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('item_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Product Information')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'fname',
            'text',
            [
                'name' => 'fname',
                'label' => __('Full Name'),
                'title' => __('Full Name'),
                'readonly' => 'true',
            ]
        );
        $fieldset = $form->addFieldset('customer_fieldset', ['legend' => __('Customer Information')]);
        
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
                'label' => __('Your Email'),
                'title' => __('Your Email'),
                'index' => 'customer_name',
                'readonly' => 'true',
            ]
        );

        $fieldset = $form->addFieldset('quote_fieldset', ['legend' => __('Inquiries Information')]);

        $fieldset->addField(
            'country',
            'text',
            [
                'name' => 'country',
                'label' => __('Country'),
                'title' => __('Country'),
                'readonly' => 'true',
            ]
        );
        $fieldset->addField(
            'product_sku',
            'text',
            [
                'name' => 'product_sku',
                'label' => __('Product SKUs'),
                'title' => __('Product SKUs'),
                'readonly' => 'true',
            ]
        );
        $fieldset->addField(
            'comment',
            'textarea',
            [
                'name' => 'comment',
                'label' => __('Comment'),
                'title' => __('Comment'),
                'readonly' => 'true',
            ]
        );

        $fieldset->addField(
            'admin_reply',
            'textarea',
            [
                'name' => 'admin_reply',
                'label' => __('Reply'),
                'title' => __('Reply'),
            ]
        );
     
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
