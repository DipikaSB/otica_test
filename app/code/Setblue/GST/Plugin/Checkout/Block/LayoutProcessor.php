<?php
namespace Setblue\GST\Plugin\Checkout\Block;

use Setblue\GST\Model\Config;

class LayoutProcessor
{
    /**
     * @var \Setblue\GST\Model\Config
     */
    protected $config;

    /**
     * LayoutProcessor constructor.
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $status =  $this->config->getstatus();
        if($status == 1){
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['gst_number'] = [
                'component' => 'uiComponent',
                //'displayArea' => 'shippingAdditional',
                'children' => [
                    'gst_number' => [
                        'component' => 'Magento_Ui/js/form/element/abstract',
                        'config' => [
                            'customScope' => 'gst_number',
                            'template' => 'ui/form/field',
                            'elementTmpl' => 'ui/form/element/input',
                            'options' => [],
                            'id' => 'gst_number'
                        ],
                        'dataScope' => 'gst_number.gst_number',
                        'label' => 'GST Number :',
                        'provider' => 'checkoutProvider',
                        'visible' => true,
                        'validation' => [],
                        'sortOrder' => 20,
                        'id' => 'gst_number'
                    ]
                ]
            ];
        }
        return $jsLayout;
    }
}
