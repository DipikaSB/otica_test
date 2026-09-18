<?php
namespace Setblue\Core\Model;

use Magento\Newsletter\Model\Subscriber as MagentoSubscriber;
use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class Subscriber extends MagentoSubscriber
{
      public function sendConfirmationSuccessEmail()
    {
        // Get image URL from last record
        $imageUrl = $this->getLatestMobileBannerImage();

        $templateVars = [
            'custom_message' => $imageUrl
                ? '<img src="' . $imageUrl . '" alt="Subscription Banner" style="max-width:100%;">'
                : 'Thank you for subscribing!',
        ];

        $this->sendCustomEmail(
            self::XML_PATH_SUCCESS_EMAIL_TEMPLATE,
            self::XML_PATH_SUCCESS_EMAIL_IDENTITY,
            $templateVars
        );

        return $this;
    }

    protected function getLatestMobileBannerImage()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('setblue_magicslider');

        $select = $connection->select()
            ->from($tableName)
            ->limit(1);

        $row = $connection->fetchRow($select);
        
        if (!$row || empty($row['config'])) {
            return null;
        }
        
        $json = $objectManager->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $data = $json->unserialize($row['config']);

        if (!isset($data['media_gallery_mobile']['images'])) {
            return null;
        }

        foreach ($data['media_gallery_mobile']['images'] as $image) {
            if (isset($image['position']) && isset($image['file'])) {
                $file = ltrim($image['file'], '/');
                $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
                $mediaBaseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                return $mediaBaseUrl .'setblue/magicslider/'. $file;
            }
        }

        return null;
    }

    public function sendCustomEmail(string $emailTemplatePath, string $emailIdentityPath, array $templateVars = [])
    {
        if ($this->getImportMode()) {
            return;
        }

        $template = $this->_scopeConfig->getValue($emailTemplatePath, ScopeInterface::SCOPE_STORE, $this->getStoreId());
        $identity = $this->_scopeConfig->getValue($emailIdentityPath, ScopeInterface::SCOPE_STORE, $this->getStoreId());

        if (!$template || !$identity) {
            return;
        }

        $templateVars += ['subscriber' => $this];

        $this->inlineTranslation->suspend();

        $this->_transportBuilder
            ->setTemplateIdentifier($template)
            ->setTemplateOptions([
                'area' => Area::AREA_FRONTEND,
                'store' => $this->getStoreId(),
            ])
            ->setTemplateVars($templateVars)
            ->setFromByScope($identity, $this->getStoreId())
            ->addTo($this->getEmail(), $this->getName());

        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();
    }
}
