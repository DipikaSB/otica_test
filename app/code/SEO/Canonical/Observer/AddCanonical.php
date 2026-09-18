<?php

namespace SEO\Canonical\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;

class AddCanonical implements ObserverInterface
{
    protected $request;
    protected $pageConfig;
    protected $scopeConfig;
    protected $urlBuilder;

    const XML_CANONICAL_MAP = 'Canonical/general/canonical_field';

    public function __construct(
        RequestInterface $request,
        PageConfig $pageConfig,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder
    ) {
        $this->request     = $request;
        $this->pageConfig  = $pageConfig;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    public function execute(Observer $observer)
    {
        $fullActionName = $this->request->getFullActionName();


        $isEnabled = $this->scopeConfig->isSetFlag(
            'Canonical/general/enabled',
            ScopeInterface::SCOPE_STORE
        );

        if (!$isEnabled) {
            return; // Exit if disabled
        }

        /** Get saved JSON from DB */
        $json = $this->scopeConfig->getValue(
            self::XML_CANONICAL_MAP,
            ScopeInterface::SCOPE_STORE
        );

        if (!$json) {
            return;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return;
        }

        /** Build page => URL map */
        $canonicalMap = [];
        foreach ($data as $row) {
            if (!empty($row['canonical_page']) && !empty($row['canonical_map_url'])) {
                $canonicalMap[$row['canonical_page']] = rtrim($row['canonical_map_url'], '/') . '/';
            }
        }

        /** If page exists in mapping, set canonical */
        if (isset($canonicalMap[$fullActionName])) {
            $this->pageConfig->addRemotePageAsset(
                $canonicalMap[$fullActionName],
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
        $currentUrl = $this->urlBuilder->getCurrentUrl();

        if ($this->request->getFullActionName() == 'cms_page_view') {
            $this->pageConfig->addRemotePageAsset(
                $currentUrl,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
    }
}
