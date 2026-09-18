<?php
/**
 * Softnoesis
 * Copyright(C) 05/2023 Softnoesis <ideveloper1990@gmail.com>
 * @package Softnoesis_MassProductReview
 * @copyright Copyright(C) 2015 Softnoesis (ideveloper1990@gmail.com)
 * @author Softnoesis <ideveloper1990@gmail.com>
 */
namespace Softnoesis\MassProductReview\Block\Adminhtml\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

class Export implements ButtonProviderInterface
{
    /**
     * @var Magento\Framework\UrlInterface
     */
    protected $url;
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var BlockRepositoryInterface
     */
    /**
     * @param UrlInterface $url
     * @param Context $context
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        Context $context
    ) {
        $this->url = $url;
        $this->context = $context;
    }
    /*Export button Page edit page*/
    public function getButtonData()
    {
         return [
            'label' => __('Custom Button'),
            'class' => 'action-secondary',
            'on_click' => 'alert("Hello World")',
            'sort_order' => 10
         ];
    }

    /*get controller url with id*/
    public function getExportUrl()
    {
        return $this->url->getUrl('import/index/exportpage');
    }
}
