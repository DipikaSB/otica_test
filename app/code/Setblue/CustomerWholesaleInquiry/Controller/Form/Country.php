<?php
/**
 * Setblue
 * Copyright(C) 04/2023 Setblue <ideveloper1990@gmail.com>
 * @package Setblue_CustomerWholesaleInquiry
 * @copyright Copyright(C) 2015 Setblue (ideveloper1990@gmail.com)
 * @author Setblue <ideveloper1990@gmail.com>
 */
namespace Setblue\CustomerWholesaleInquiry\Controller\Form;
 
class Country extends \Magento\Framework\App\Action\Action
{
         protected $resultJsonFactory;
    
    protected $regionColFactory;
 
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Directory\Model\RegionFactory $regionColFactory
    ) {
        $this->regionColFactory     = $regionColFactory;
        $this->resultJsonFactory     = $resultJsonFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
 
        $result           = $this->resultJsonFactory->create();
        $regions=$this->regionColFactory->create()->getCollection()->addFieldToFilter('country_id', $this->getRequest()->getParam('country'));
         
        $html = '';
         
        if (count($regions) > 0) {
                     $html.='<option name="country" id="country" selected="selected" value="">Please select a region, state or province.</option>';
            foreach ($regions as $state) {
                        $html.=    '<option  value="'.$state->getName().'">'.$state->getName().'.</option>';
            }
        }
             return $result->setData(['success' => true,'value'=>$html]);
    }
}
