<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_GuestToCustomer
 * @copyright   Copyright (c) 2019 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */

namespace Ecomteck\GuestToCustomer\Block\Adminhtml\System\Config;

/**
 * Class ConvertButtonField
 * @package Ecomteck\GuestToCustomer\Block\Adminhtml\System\Config
 */
class ConvertButtonField extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Backend\Block\Template\Context $context, 
        array $data = [])
    {
        $this->_backendUrl = $backendUrl;
        parent::__construct($context, $data);
    }
	/**
     * Add color picker
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = "";
        $url = $this->_backendUrl->getUrl("ecguesttocustomer/all/convert", []);
        $html .= '
        <div class="pp-buttons-container"><button type="button" id="convert_all_cust"><span><span><span>'.__("Convert All Old Guests To Customer").'</span></span></span></button></div>';
        $html .= "
        <script>
        require([
                'jquery',
                'Magento_Ui/js/modal/confirm'
            ],
            function($, confirmation) {
                $('#convert_all_cust').on('click', function(event){
                    event.preventDefault;
                    confirmation({
                        title: '".__("You are really want to convert all guests to customers?")."',
                        content: '',
                        actions: {
                            confirm: function () {
                                window.location='".$url."';
                            },
                        }
                    });
                });
            });</script>";
    	return $html;
    }
}
