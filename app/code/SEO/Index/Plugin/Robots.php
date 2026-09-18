<?php

namespace SEO\Index\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\Renderer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Robots
{
    protected $pageConfig;
    protected $request;
    protected $scopeConfig;

    public function __construct(
        PageConfig $pageConfig,
        Http $request,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->pageConfig = $pageConfig;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }

    public function beforeRenderMetadata(Renderer $subject)
    {
        $fullActionName = $this->request->getFullActionName();

        // Check if the feature is enabled in system config
        $isEnabled = $this->scopeConfig->isSetFlag(
            'dynamicrow/general/enabled',
            ScopeInterface::SCOPE_STORE
        );

        if (!$isEnabled) {
            return; // Exit if disabled
        }

        // Get dynamic list of NOINDEX pages from system config
        $noIndexPagesConfig = $this->scopeConfig->getValue(
            'dynamicrow/general/noindex_nofollow_field',
            ScopeInterface::SCOPE_STORE
        );

        // Decode JSON if needed
        $noIndexPages = json_decode($noIndexPagesConfig, true);

        if (!is_array($noIndexPages)) {
            $noIndexPages = [];
        }

        // Extract only the action names
        $actionNames = [];
        foreach ($noIndexPages as $item) {
            if (isset($item['noindex_nofollow'])) {
                $actionNames[] = $item['noindex_nofollow'];
            }
        }


        if (in_array($fullActionName, $actionNames, true)) {
            $this->pageConfig->setMetadata('robots', 'NOINDEX,NOFOLLOW');
        }
    }

}
