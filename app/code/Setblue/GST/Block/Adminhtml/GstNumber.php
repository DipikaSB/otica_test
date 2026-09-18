<?php
namespace Setblue\GST\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;

class GstNumber extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Json
     */
    private $json;

    /**
     * GST Number constructor.
     *
     * @param Context $context
     * @param Config $config
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        Json $json,
        array $data = []
    ) {
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->json->serialize($this->config->getConfig());
    }

}