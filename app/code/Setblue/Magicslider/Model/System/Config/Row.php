<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */

namespace Setblue\Magicslider\Model\System\Config;

class Row implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            '0'=>   __('1 row(s) without warp /slider'),
            '1'=>   __('1 row(s) /slider'),
            '2'=>   __('2 row(s) /slider'),
            '3'=>   __('3 row(s) /slider'),
            '4'=>   __('4 row(s) /slider'),
            '5'=>   __('5 row(s) /slider'),
        ];
    }
}
