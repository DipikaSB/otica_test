<?php
namespace SEO\GMC\Model;

use Magento\Store\Model\StoreManagerInterface;

class StoreOptions implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function toOptionArray()
    {
        $options = [];

        foreach ($this->storeManager->getStores() as $store) {
            $options[] = [
                'value' => $store->getId(),
                'label' => $store->getName()
            ];
        }

        return $options;
    }
}
