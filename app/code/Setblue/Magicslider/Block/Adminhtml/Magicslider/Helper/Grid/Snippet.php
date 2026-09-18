<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */

namespace Setblue\Magicslider\Block\Adminhtml\Magicslider\Helper\Grid;

class Snippet extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Magicslider factory.
     *
     * @var \Setblue\Magicslider\Model\MagicsliderFactory
     */
    protected $magicsliderFactory;

    /**
     *
     * @param \Magento\Backend\Block\Context              $context
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Setblue\Magicslider\Model\MagicsliderFactory $magicsliderFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->magicsliderFactory  = $magicsliderFactory;
    }

    /**
     * Render action.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $storeViewId = $this->getRequest()->getParam('store');
        $item = $this->magicsliderFactory->create()->setStoreViewId($storeViewId)->load($row->getId());
        $identifier = $item->getIdentifier();
        $shortcodeWidget = $this->_escaper->escapeHtml('{{widget type="Setblue\Magicslider\Block\Widget\Slider" identifier="' . $identifier . '" template="magicslider.phtml"}}');
        $shortcodeBlock  = $this->_escaper->escapeHtml('<?= $block->getLayout()->createBlock(\'Setblue\Magicslider\Block\Widget\Slider\')->setIdentifier("' . $identifier . '")->setTemplate(\'magicslider.phtml\')->toHtml(); ?>');
        $emojiCopy = '<span style="font-size:30px">✂️</span>';
        $html = '<div class="setblue-snippet" style="display:inline-block;width:150px; float:left"><input class="copy-input" type="hidden" value="' . $shortcodeWidget . '" readonly><button style="display: inline-flex" class="copy-to-clipboard action-default scalable add primaryx">' . __('Copy to Page|Block') . $emojiCopy . '</button></div>';
        $html .= '<div class="setblue-snippet" style="display:inline-block;width:150px; float:right"><input class="copy-input" type="hidden" value="' . $shortcodeBlock . '" readonly><button style="display: inline-flex" class="copy-to-clipboard action-default scalable add primaryx">' . __('Copy to .phtml') . $emojiCopy . '</button></div>';

        return $html;
    }
}