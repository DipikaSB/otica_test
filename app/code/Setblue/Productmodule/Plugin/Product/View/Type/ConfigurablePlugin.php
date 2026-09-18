<?php 
namespace Setblue\Productmodule\Plugin\Product\View\Type; 
use Magento\Catalog\Helper\Output as CatalogOutputHelper;
use Magento\Store\Model\StoreManagerInterface;

class ConfigurablePlugin { 
     protected $jsonEncoder; 
     protected $jsonDecoder; 
     protected $outputHelper;
     protected $imageHelper;
     protected $storeManager;

     public function __construct( 
        \Magento\Framework\Json\DecoderInterface $jsonDecoder, 
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        CatalogOutputHelper $outputHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        StoreManagerInterface $storeManager
    ){ 
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->outputHelper = $outputHelper;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
    }

    public function afterGetJsonConfig(\Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject, $result) 
    {
        $result = $this->jsonDecoder->decode($result);
        $currentProduct = $subject->getProduct();

        if ($currentProduct->getName()) {
            $result['productName'] = $currentProduct->getName();
        }

        if ($currentProduct->getSku()) {
            $result['productSku'] = $currentProduct->getSku();
        }
        
        foreach ($subject->getAllowProducts() as $product) {

            $result['names'][$product->getId()] = $product->getName();
            $result['skus'][$product->getId()] = $product->getSku();
            // Store the sanitized descriptionHtml in the result array
            $result['desc'][$product->getId()] = $product->getDescription();
            $result['shortdesc'][$product->getId()] = $product->getShortDescription();

        }
        return $this->jsonEncoder->encode($result);
    }
}