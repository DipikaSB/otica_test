<?php

namespace Meetanshi\Paymulti\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class CheckoutWith implements ArrayInterface
{
    public function toOptionArray()
    {
        $methods = ['0' => ['label' => 'Checkout With Current Currency', 'value' => 0],
            '1' => ['label' => 'Checkout With Allowed Currency', 'value' => 1]
        ];

        return $methods;
    }
}
