<?php

namespace SEO\GMC\Ui\Component\MassAction;

use Magento\Store\Api\WebsiteRepositoryInterface;

class Websites implements \JsonSerializable
{
    protected $websiteRepository;

    public function __construct(
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->websiteRepository = $websiteRepository;
    }

    public function jsonSerialize(): mixed
    {
        $items = [];

        foreach ($this->websiteRepository->getList() as $website) {

            // skip admin website
            if ((int)$website->getId() === 0) {
                continue;
            }

            $items[] = [
                'type'  => 'website_' . $website->getId(),
                'label' => $website->getName(),
                'url'   => $this->getUrl('seo_gmc/product/massexport', [
                    'website_id' => $website->getId()
                ])
            ];
        }

        return $items;
    }


    private function getUrl(string $route, array $params = [])
    {
        $url = '/a2294a63ee_admin/' . $route;

        foreach ($params as $key => $value) {
            $url .= '/' . $key . '/' . $value;
        }

        return $url;
    }

}
