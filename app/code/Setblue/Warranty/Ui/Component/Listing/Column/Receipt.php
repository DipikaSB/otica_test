<?php

namespace Setblue\Warranty\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Receipt extends Column
{
    protected $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {

                if (!empty($item['purchase_receipt'])) {

                    $file = $item['purchase_receipt'];
                    $mediaUrl = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . $file;

                    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                        // Show image thumbnail
                        $item['purchase_receipt'] =
                            '<a href="' . $mediaUrl . '" target="_blank">
                                <img src="' . $mediaUrl . '" width="60" height="60" style="border:1px solid #ccc;"/>
                             </a>';
                    }
                    elseif ($extension == 'pdf') {
                        // Show PDF icon
                        $pdfIcon = $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) 
                                    . 'warranty/pdf-icon.png';

                        $item['purchase_receipt'] =
                            '<a href="' . $mediaUrl . '" target="_blank">
                                <img src="' . $pdfIcon . '" width="30" style="margin-right:5px;"/>
                             </a>';
                    }
                }
            }
        }
        return $dataSource;
    }
}