<?php

/**
 * SetBlue
 *
 * Copyright (C) 2026 SetBlue <info@setblue.com>
 *
 * @category  SetBlue
 * @package   SEO_HreflangSitemap
 * @copyright Copyright (c) 2026 SetBlue (https://setblue.com/)
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author    SetBlue <info@setblue.com>
 */

namespace SEO\HreflangSitemap\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class ConfigChange implements ObserverInterface
{
    protected $scopeConfig;
    protected $file;
    protected $directoryList;
    protected $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        File $file,
        DirectoryList $directoryList,
        LoggerInterface $logger
    ) {
        $this->scopeConfig   = $scopeConfig;
        $this->file          = $file;
        $this->directoryList = $directoryList;
        $this->logger        = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $isEnabled = $this->scopeConfig->getValue(
                'sitemap/hreflangsitemap/enabled',
                ScopeInterface::SCOPE_STORE
            );

            if (!$isEnabled) {
                $this->logger->info('Hreflang sitemap module is disabled.');
                return;
            }

            $sitemapXml = $this->scopeConfig->getValue(
                'sitemap/hreflangsitemap/sitemap_xml'
            );

            if (!$sitemapXml) {
                return;
            }

            $fileFromConfig = $this->scopeConfig->getValue(
                'sitemap/hreflangsitemap/sitemap_file'
            );

            if (!$fileFromConfig) {
                throw new \Exception('Sitemap file path is not configured');
            }

            $rootPath = $this->directoryList->getPath(DirectoryList::ROOT);
            $filePath = $rootPath . '/' . ltrim($fileFromConfig, '/');

            $dirPath = dirname($filePath);
            if (!$this->file->isExists($dirPath)) {
                $this->file->createDirectory($dirPath);
            }

            $this->file->filePutContents($filePath, $sitemapXml);

            $this->logger->info(
                'Hreflang sitemap generated at: ' . $filePath
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
