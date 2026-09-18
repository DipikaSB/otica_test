<?php

/**
 * Athlete2 Theme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2025 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\Athlete2\Block;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template;
use Olegnax\Athlete2\Model\DynamicStyle\EscapeCss;

class SocialIcons extends Template implements BlockInterface
{
    /**
     * @var EscapeCss
     */
    protected $escapeCss;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        EscapeCss $escapeCss,
        Json $json,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->escapeCss = $escapeCss;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    public function getCacheKeyInfo($newval = [])
    {
        return array_merge([
            'OLEGNAX_SOCIALICONS_WIDGET',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $this->json->serialize($this->getData()),
        ], parent::getCacheKeyInfo(), $newval);
    }

    public function getSocialsId()
    {
        return 'ox_' . $this->getNameInLayout();
    }

    public function getSocialLinks()
    {
        $socials = [
            'facebook',
			'facebook_messenger',
			'instagram',
			'twitter',
            'pinterest',
			'skype',
			'tumblr',
			'youtube',
			'amazon',
			'amazon_pay',
			'kickstarter',
			'stripe',
			'paypal',
			'vimeo',
			'vk',
			'foursquare',
			'flickr',
			'linkedin',
			'whatsapp',
			'telegram_plane',
			'snapchat',
			'reddit',
			'discord',
			'slack',
			'tripadvisor',
			'tiktok',
			'business'
        ];

        $socialLinks = [];
        $socialOrder = [];

        foreach ($socials as $social) {
            $link = $this->getData($social . '_link');
            if ($link) {
                $socialLinks[$social] = $link;
                $order = (int)($this->getData($social . '_sort') ?? 0);
                $socialOrder[$social] = abs($order);
            }
        }

        asort($socialOrder);
        $result = [];
        foreach (array_keys($socialOrder) as $social) {
            $result[$social] = $socialLinks[$social];
        }

        return $result;
    }

    public function prepareStyle(array $style, string $separatorValue = ': ', string $separatorAttribute = ';')
    {
        $filteredStyle = array_filter($style);
        if (empty($filteredStyle)) {
            return '';
        }

        $styleString = [];
        foreach ($filteredStyle as $key => $value) {
            $styleString[] = "{$key}{$separatorValue}{$value}";
        }

        return implode($separatorAttribute, $styleString);
    }

    public function prepareStyleBlock(array $style)
    {
        $result = [];

        foreach ($style as $selector => $_style) {
            $preparedStyle = $this->prepareStyle($_style);
            if (!empty($preparedStyle)) {
                $result[$selector] = "{$selector}{{$preparedStyle}}";
            }
        }

        return !empty($result) ? $this->renderStyles(implode("\n", $result)) : '';
    }

    /**
     * Render Inline styles.
     *
     * @param string $styles CSS styles to render.
     * @return string Rendered CSS styles wrapped in style tags.
     */
    public function renderStyles($styles = ''){
        return $this->escapeCss->renderStyles($styles);
    }
}