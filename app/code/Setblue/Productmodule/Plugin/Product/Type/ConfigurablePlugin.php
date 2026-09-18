<?php 
namespace Setblue\Productmodule\Plugin\Product\Type;

class ConfigurablePlugin
{
    public function afterGetUsedProductCollection(\Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject, $result)
   {
        $result->addAttributeToSelect('description');
        $result->addAttributeToSelect('price');
        $result->addAttributeToSelect('sku');

        $result->addAttributeToSelect('short_description');
        
       return $result;
   }
}