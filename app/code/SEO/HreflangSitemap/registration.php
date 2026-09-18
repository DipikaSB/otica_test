<?php

/**
 * SetBlue
 *
 * Copyright (C) 2026 SetBlue <info@setblue.com>
 *
 * @category  SetBlue
 * @package   SEO_HreflangSitemap
 * @copyright Copyright (c) 2026 SetBlue (https://setblue.com/)
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author    SetBlue <info@setblue.com>
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'SEO_HreflangSitemap',
    __DIR__
);
