<?php
namespace Setblue\ProductWidget\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Reports\Block\Product\Viewed as recentlyViewed;

class Productbysku extends Template implements BlockInterface
{
    protected $_template = "Setblue_ProductWidget::product_by_sku.phtml";
    protected $productCollectionFactory;
    protected $imageHelper;
    protected $recentlyViewed;

    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        recentlyViewed $recentlyViewed,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->recentlyViewed = $recentlyViewed;
        parent::__construct($context, $data);
    }

    public function getProductBySkuCollection($sku)
    {
        $skuString = $this->getData('sku');
        if (!$skuString) {
            return [];
        }

        $skus = array_map('trim', explode(',', $skuString));

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addFieldToFilter('sku', ['in' => $skus])
            ->addFieldToFilter('visibility', ['neq' => Visibility::VISIBILITY_NOT_VISIBLE])
            ->getSelect()
            ->order('RAND()') 
            ->limit(20); 

        return $collection;
    }

    public function getProductByCategoryCollection($categoryIds = [])
    {
        if (empty($categoryIds)) {
            return [];
        }

        // Convert comma-separated string to array
        if (is_string($categoryIds)) {
            $categoryIds = array_map('trim', explode(',', $categoryIds));
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addCategoriesFilter(['in' => $categoryIds])
            ->addFieldToFilter('visibility', ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]);

        // Randomize and limit to 3
        $collection->getSelect()->order('RAND()');
        $collection->getSelect()->limit(15);

        return $collection;
    }
    public function getProductByCategoryBlog($categoryIds = [])
    {
        if (empty($categoryIds)) {
            return [];
        }

        // Convert comma-separated string to array
        if (is_string($categoryIds)) {
            $categoryIds = array_map('trim', explode(',', $categoryIds));
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addCategoriesFilter(['in' => $categoryIds])
            ->addFieldToFilter('visibility', ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]);

        // Randomize and limit to 3
        $collection->getSelect()->order('RAND()');
        $collection->getSelect()->limit(10);

        return $collection;
    }
    /**
     * Get product image URL
     */
    public function getProductImageUrl($product, $imageId = 'category_page_grid')
    {
        return $this->imageHelper->init($product, $imageId)->getUrl();
    }
    public function getMostRecentlyViewed(){
        return $this->recentlyViewed->getItemsCollection()->getData();
    }
}