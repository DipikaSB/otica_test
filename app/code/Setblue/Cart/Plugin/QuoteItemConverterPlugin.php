<?php
namespace Setblue\Cart\Plugin;

use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Quote\Api\Data\TotalsItemExtensionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\StoreManagerInterface;


class QuoteItemConverterPlugin
{
    /**
     * @var TotalsItemExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * @var StoreManagerInterface
    */
    protected $storeManager;

    public function __construct(
        TotalsItemExtensionFactory $extensionFactory,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
    }

    /**
     * Add discountPercentage and final price (converted currency)
     *
     * @param ItemConverter $subject
     * @param TotalsItemInterface $result
     * @param QuoteItem $item
     * @return TotalsItemInterface
     */
    public function afterModelToDataObject(
        ItemConverter $subject,
        TotalsItemInterface $result,
        QuoteItem $item
    ) {
        /** Get or create extension attributes */
        $extensionAttributes = $result->getExtensionAttributes()
            ?: $this->extensionFactory->create();

        /** Product */
        $product = $item->getProduct();

        /** Base catalog price (base currency) */
        $productPrice = (float) $product->getPrice();
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        if ($currencyCode != 'INR') {
            $productPrice = (float) $this->priceCurrency->convert($product->getPrice(),$item->getStoreId());
        }

        /** Quote item price (base currency) */
        $finalPrice = (float) $item->getCalculationPrice();

        /** Discount percentage */
        $discountPercentage = 0;
        if ($productPrice > 0 && $finalPrice < $productPrice) {
            $discountPercentage = round(
                (($productPrice - $finalPrice) / $productPrice) * 100
            );
        }

        /** Set discount % */
        $extensionAttributes->setProductDiscountPrice($discountPercentage);

        /** Offer price (base currency) */
        $offerPrice = 0;
        if ($productPrice != $finalPrice) {
            $offerPrice = $productPrice * $item->getQty();
        }

        /** Convert to store currency */
        $convertedOfferPrice = $this->priceCurrency->convert(
            $offerPrice,
            $item->getStoreId()
        );

        /** Set converted price */
        $extensionAttributes->setProductFinalPrice(
            round($convertedOfferPrice, 2)
        );

        /** Attach extension attributes */
        $extensionAttributes->setProductFinalPrice(
            round($productPrice*$item->getQty(), 2)
        );
        $result->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}