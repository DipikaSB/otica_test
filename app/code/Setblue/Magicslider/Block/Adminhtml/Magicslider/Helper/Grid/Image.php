<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */
namespace Setblue\Magicslider\Block\Adminhtml\Magicslider\Helper\Grid;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Magicslider factory.
     *
     * @var \Setblue\Magicslider\Model\MagicsliderFactory
     */
    protected $_magicsliderFactory;

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
        $this->_storeManager = $storeManager;
        $this->_magicsliderFactory  = $magicsliderFactory;
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
        $item = $this->_magicsliderFactory->create()->setStoreViewId($storeViewId)->load($row->getId());
        $data = json_decode($item->getConfig() ?? '', true);
        if(!isset($data['media_gallery'])) return '<span>' . __('No media.') . '</span>';
        $gallery = $data['media_gallery'];
        $_images = $gallery['images'];
        $src     ='';
        if(is_array($_images)){
            foreach ($_images as $image) {
                $src = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'setblue/magicslider' . $image['file'];
                break;
            }            
        }
        return '<image style="max-width:100px;" src ="'.$src.'" alt="Preview" >';
    }
}
