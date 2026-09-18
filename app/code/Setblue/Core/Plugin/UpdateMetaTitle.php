<?php

namespace Setblue\Core\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class UpdateMetaTitle
{
    protected $pageConfig;
    protected $productRepository;
    protected $request;
    public $scopeConfig;
    protected $storeManagerInterface;
    protected $categoryRepository;
    

    public function __construct(
        PageConfig $pageConfig,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManagerInterface,
        CategoryRepositoryInterface $categoryRepository

    ) {
        $this->pageConfig = $pageConfig;
        $this->productRepository = $productRepository;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->categoryRepository = $categoryRepository;
    }

    public function aroundExecute(
        \Magento\Catalog\Controller\Product\View $subject,
        \Closure $proceed
    ) {
        $result = $proceed();

        // Get product ID safely
        $productId = (int) $this->request->getParam('id') ?: (int) $this->request->getParam('product_id');
        
        if ($productId) {
            /** @var Product $product */
            $product = $this->productRepository->getById($productId);

            $storecode = $this->storeManagerInterface->getStore()->getCode();

            $configValue = $this->scopeConfig->getValue(
                'catalog/frontend/customer_fields_mapping',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            // If it's JSON, decode; otherwise, keep as array
            if (is_string($configValue)) {
                $countryMap = json_decode($configValue, true);
            } else {
                $countryMap = $configValue;
            }

            // If decoding failed, make sure it's an array
            if (!is_array($countryMap)) {
                $countryMap = [];
            }

            // Directly use the store code mapping
            $countryName = $countryMap[$storecode] ?? $countryMap['default'] ?? 'India';

            // Custom meta title
            $customMetaTitle = 'Buy ' . $product->getName() . ' Online in ' . $countryName . ' | Otica Healthcare';
            
            // Sets <title> tag
            $this->pageConfig->getTitle()->set($customMetaTitle);

            // If you want <meta name="title"> you have to add it manually:
            $this->pageConfig->setMetadata('title', $customMetaTitle);

            $categoryIds = $product->getCategoryIds();

            if (!empty($categoryIds)) {
                // Get first assigned category (you can change logic if needed)
                $category = $this->categoryRepository->get($categoryIds[1]);
                $categoryName = $category->getName();
            }

            $customMetaDescription = 'Shop ' . $product->getName() . ' online in ' . $countryName . ' from Otica Healthcare, a trusted manufacturer and supplier. Explore our comprehensive range of ' . $categoryName . ' designed for professional medical use and home care.';

            $this->pageConfig->setDescription($customMetaDescription);
        }

        return $result;
    }
}
