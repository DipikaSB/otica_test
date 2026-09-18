<?php
namespace Phonepe\PG\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class DisplayMode implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'iframe', 'label' => __('IFrame (Inline)')],
            ['value' => 'redirect', 'label' => __('Redirect (Full Page)')],
        ];
    }
}
