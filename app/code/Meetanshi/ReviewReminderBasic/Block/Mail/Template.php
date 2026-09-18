<?php

namespace Meetanshi\ReviewReminderBasic\Block\Mail;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Url;
use Magento\Framework\View\Element\Template as CoreTempate;
use Meetanshi\ReviewReminderBasic\Helper\Data;

class Template extends CoreTempate
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Image
     */
    private $imageHelperFactory;

    /**
     * @var Url
     */
    private $frontendUrlBuilder;

    /**
     * Template constructor.
     *
     * @param CoreTempate\Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Data $helper
     * @param Image $imageHelperFactory
     * @param Url $frontendUrlBuilder
     * @param array $data
     */
    public function __construct(
        CoreTempate\Context $context,
        CollectionFactory $productCollectionFactory,
        Data $helper,
        Image $imageHelperFactory,
        Url $frontendUrlBuilder,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->helper = $helper;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        CoreTempate::__construct($context, $data);
    }

    /**
     * Get reminder product data.
     *
     * @param mixed $reminder
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getReminderData(mixed $reminder)
    {

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')->addFieldToFilter('entity_id', ['in' => $reminder['product_id']]);

        return $collection;
    }

    /**
     * Get UTM configuration.
     *
     * @return bool|string
     */
    public function getUtm()
    {
        return $this->helper->getUtmConfig();
    }

    /**
     * Get product image URL.
     *
     * @param mixed $product
     *
     * @return string
     */
    public function getProductImage(mixed $product)
    {
        try {

            return $this->imageHelperFactory->init($product, 'small_image', ['type' => 'small_image'])->keepAspectRatio(true)->resize('65', '65')->getUrl();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get the correct frontend review URL for a product.
     *
     * Uses the frontend URL builder to ensure the URL always points to the
     * storefront, even when the email is triggered from the admin area.
     *
     * @param int $productId
     * @param string $incrementId
     * @param string $customerName
     *
     * @return string
     */
    public function getReviewUrl($productId, $incrementId, $customerName = '')
    {
        $params = [
            'product_id' => $productId,
            'order' => $incrementId,
            '_nosid' => true,
        ];
        if (!empty($customerName)) {
            $params['customer_name'] = base64_encode($customerName);
        }
        return $this->frontendUrlBuilder->getUrl(
            'reviewreminder/review/add',
            $params
        );
    }
}
