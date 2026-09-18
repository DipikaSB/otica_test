<?php

namespace SEO\HreflangSitemap\Model;

use DOMDocument;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Sitemap\Helper\Data as SitemapData;
use Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;
use Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as ModelDate;

class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    protected $scopeConfig;

    public function __construct(
        Context $context,
        Registry $registry,
        Escaper $escaper,
        SitemapData $sitemapData,
        Filesystem $filesystem,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory,
        PageFactory $cmsFactory,
        ModelDate $modelDate,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;

        parent::__construct(
            $context,
            $registry,
            $escaper,
            $sitemapData,
            $filesystem,
            $categoryFactory,
            $productFactory,
            $cmsFactory,
            $modelDate,
            $storeManager,
            $request,
            $dateTime,
            $resource,
            $resourceCollection,
            $data
        );
    }
    /**
     * Override _initSitemapItems method
     */
    protected function _initSitemapItems()
    {
        $sitemapItems = $this->itemProvider->getItems($this->getStoreId());
        $mappedItems  = $this->mapToSitemapItem();

        $this->_sitemapItems = array_merge($sitemapItems, $mappedItems);

        $isEnabled = $this->scopeConfig->getValue(
            'sitemap/hreflangsitemap/enabled',
            ScopeInterface::SCOPE_STORE
        );

        if ($isEnabled) {
            $this->_tags = [
                self::TYPE_INDEX => [
                    self::OPEN_TAG_KEY  => '<?xml version="1.0" encoding="UTF-8"?>'
                        . PHP_EOL
                        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
                        . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
                        . 'xmlns:xhtml="http://www.w3.org/1999/xhtml">'
                        . PHP_EOL,
                    self::CLOSE_TAG_KEY => '</sitemapindex>',
                ],
                self::TYPE_URL => [
                    self::OPEN_TAG_KEY  => '<?xml version="1.0" encoding="UTF-8"?>'
                        . PHP_EOL
                        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
                        . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
                        . 'xmlns:xhtml="http://www.w3.org/1999/xhtml">'
                        . PHP_EOL,
                    self::CLOSE_TAG_KEY => '</urlset>',
                ],
            ];
        }else {
            $this->_tags = [
                self::TYPE_INDEX => [
                    self::OPEN_TAG_KEY =>
                        '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
                        '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
                        PHP_EOL,
                    self::CLOSE_TAG_KEY => '</sitemapindex>',
                ],
                self::TYPE_URL => [
                    self::OPEN_TAG_KEY =>
                        '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL .
                        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' .
                        'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' .
                        PHP_EOL,
                    self::CLOSE_TAG_KEY => '</urlset>',
                ],
            ];
        }
    }

    /**
     * Override _getSitemapRow method
     */
    protected function _getSitemapRow(
        $url,
        $lastmod = null,
        $changefreq = null,
        $priority = null,
        $images = null
    ) {
        $url = $this->_getUrl($url);
        $row = '<loc>' . $this->_escaper->escapeUrl($url) . '</loc>';

        if ($lastmod) {
            $row .= '<lastmod>' . $this->_getFormattedLastmodDate($lastmod) . '</lastmod>';
        }
        $isEnabled = $this->scopeConfig->getValue(
            'sitemap/hreflangsitemap/enabled',
            ScopeInterface::SCOPE_STORE
        );

        if ($isEnabled) {
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '';

            // Remove store code from path (ca, in, uk, etc.)
            $path = preg_replace('#^/[^/]+#', '', $path);

            foreach ($this->_storeManager->getStores() as $store) {
                if (!$store->isActive()) {
                    continue;
                }

                $hreflang = $this->getHrefLangFromStoreCode($store->getCode());
                $storeBaseUrl = rtrim($store->getBaseUrl(), '/');

                $row .= sprintf(
                    '<xhtml:link rel="alternate" hreflang="%s" href="%s"/>',
                    $hreflang,
                    $this->_escaper->escapeUrl($storeBaseUrl . $path)
                );
            }
        }

        if ($changefreq) {
            $row .= '<changefreq>' . $this->_escaper->escapeHtml($changefreq) . '</changefreq>';
        }

        if ($priority) {
            $row .= sprintf(
                '<priority>%.1f</priority>',
                $this->_escaper->escapeHtml($priority)
            );
        }

        if ($images) {
            // Add Images to sitemap
            foreach ($images->getCollection() as $image) {
                $row .= '<image:image>';
                $row .= '<image:loc>' . $this->_escaper->escapeUrl($image->getUrl()) . '</image:loc>';
                $row .= '<image:title>' . $this->escapeXmlText($images->getTitle()) . '</image:title>';

                if ($image->getCaption()) {
                    $row .= '<image:caption>'
                        . $this->escapeXmlText($image->getCaption())
                        . '</image:caption>';
                }

                $row .= '</image:image>';
            }

            // Add PageMap image for Google web search
            $row .= '<PageMap xmlns="http://www.google.com/schemas/sitemap-pagemap/1.0">'
                . '<DataObject type="thumbnail">';
            $row .= '<Attribute name="name" value="'
                . $this->_escaper->escapeHtmlAttr($images->getTitle())
                . '"/>';
            $row .= '<Attribute name="src" value="'
                . $this->_escaper->escapeUrl($images->getThumbnail())
                . '"/>';
            $row .= '</DataObject></PageMap>';
        }

        return '<url>' . $row . '</url>';
    }

    protected function getHrefLangFromStoreCode(string $code): string
    {
        $hreflangConfig = $this->scopeConfig->getValue(
            'sitemap/hreflangsitemap/store_hreflang_mapping',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        if (!$hreflangConfig) {
            return 'en';
        }

        // Convert config string to array
        // uk:en-gb,us:en-us → ['uk' => 'en-gb', 'us' => 'en-us']
        $mapping = [];
        foreach (explode(',', $hreflangConfig) as $pair) {
            [$storeCode, $lang] = array_map('trim', explode(':', $pair));
            $mapping[$storeCode] = $lang;
        }

        if ($code === 'base') {
            return 'x-default';
        }

        return $mapping[$code] ?? 'x-default';
    }

    private function mapToSitemapItem()
    {
        $items = [];

        foreach ($this->_sitemapItems as $data) {
            foreach ($data->getCollection() as $item) {
                $items[] = $this->sitemapItemFactory->create([
                    'url'             => $item->getUrl(),
                    'updatedAt'       => $item->getUpdatedAt(),
                    'images'          => $item->getImages(),
                    'priority'        => $data->getPriority(),
                    'changeFrequency' => $data->getChangeFrequency(),
                ]);
            }
        }

        return $items;
    }

    private function escapeXmlText(string $text): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $fragment = $doc->createDocumentFragment();
        $fragment->appendChild($doc->createTextNode($text));

        return $doc->saveXML($fragment);
    }
}
