<?php

namespace Setblue\ContactUsGrid\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store as SystemStore;

class Store implements OptionSourceInterface
{
    /**
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * Store constructor.
     *
     * @param SystemStore $systemStore
     */
    public function __construct(
        SystemStore $systemStore
    ) {
        $this->systemStore = $systemStore;
    }

    /**
     * Get store options as an array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['label' => '-- Please Select --', 'value' => '']];
        $storeValues = $this->systemStore->getStoreValuesForForm();

        foreach ($storeValues as $store) {
            $options[] = [
                'label' => $store['label'],
                'value' => $store['value'],
            ];
        }

        return $options;
    }
}
