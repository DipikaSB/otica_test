<?php
/**
 * @copyright Copyright (c) 2015 Setblue Limited (http://www.setblue.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Setblue_PrintOrderPdf',
    __DIR__
);
