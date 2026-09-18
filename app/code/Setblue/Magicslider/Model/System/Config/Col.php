<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */

namespace Setblue\Magicslider\Model\System\Config;

class Col implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            '1'=>   __('1 item(s) /row'),
            '2'=>   __('2 item(s) /row'),
            '3'=>   __('3 item(s) /row'),
            '4'=>   __('4 item(s) /row'),
            '5'=>   __('5 item(s) /row'),
            '6'=>   __('6 item(s) /row'),
            '7'=>   __('7 item(s) /row'),
            '8'=>   __('8 item(s) /row'),
        ];
    }
}
