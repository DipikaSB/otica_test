<?php
namespace Phonepe\PG\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Phonepe\PG\Model\Constants\EnvironmentConstants;

class Environment implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => EnvironmentConstants::UAT, 'label' => __('Test')],
            ['value' => EnvironmentConstants::PRODUCTION, 'label' => __('Production')],
        ];
    }
}
