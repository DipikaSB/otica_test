<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */

namespace Setblue\Magicslider\Model\Widget\Config;

class Slider implements \Magento\Framework\Option\ArrayInterface
{

	protected $scopeConfig;
	protected $_magicslider;

	public function __construct(
		// \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Setblue\Magicslider\Model\Magicslider $magicslider
	)
	{
		$this->_magicslider = $magicslider;
	}

    public function toOptionArray()
    {
		$magicslider = $this->_magicslider->getCollection();
		$options = array();
		foreach ($magicslider as $item) {
			$options[$item->getIdentifier()] = $item->getTitle();
		}
        return $options;
    }

}
