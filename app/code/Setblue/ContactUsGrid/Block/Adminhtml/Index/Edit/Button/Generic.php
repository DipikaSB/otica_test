<?php

namespace Setblue\ContactUsGrid\Block\Adminhtml\Index\Edit\Button;

use Magento\Backend\Block\Widget\Context;

class Generic
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Generic constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

     /**
      * Get URL using the provided route and parameters.
      *
      * @param string $route
      * @param array  $params
      * @return string
      */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
