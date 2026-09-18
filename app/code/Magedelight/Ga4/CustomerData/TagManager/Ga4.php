<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\CustomerData\TagManager;

use Magento\Framework\Json\Helper\Data;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

class Ga4 extends \Magento\Framework\DataObject implements \Magento\Customer\CustomerData\SectionSourceInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $helper;

    /**
     * @param Data $helper
     * @param CheckoutSession $checkoutSession
     * @param ustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Data $helper,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        parent::__construct($data);
    }

    /**
     * GetSectionData
     */
    public function getSectionData()
    {
        $result = [];

        //Add to Cart//
        if ($this->checkoutSession->getAddToCartData()) {
            $result[] = $this->checkoutSession->getAddToCartData();
        }
        $this->checkoutSession->setAddToCartData(null);

        //Add to Wishlist//
        if ($this->customerSession->getAddToWishListData()) {
            $result[] = $this->customerSession->getAddToWishListData();
        }
        $this->customerSession->setAddToWishListData(null);

        //Add to Compare//
        if ($this->customerSession->getAddToCompareData()) {
            $result[] = $this->customerSession->getAddToCompareData();
        }
        $this->customerSession->setAddToCompareData(null);

        //Payment and Shipping on checkout//
        if ($this->checkoutSession->getCheckoutOptionsData()) {
            $checkoutOptions = $this->checkoutSession->getCheckoutOptionsData();
            foreach ($checkoutOptions as $options) {
                $result[] = $options;
            }
        }
        $this->checkoutSession->setCheckoutOptionsData(null);

        //Remove Items From Cart//
        if ($this->checkoutSession->getRemoveFromCartData()) {
            $result[] = $this->checkoutSession->getRemoveFromCartData();
        }
        $this->checkoutSession->setRemoveFromCartData(null);

        return ['datalayer' => $this->helper->jsonEncode($result)];
    }
}
