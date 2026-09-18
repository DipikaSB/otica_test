<?php
/**
 * Custom Column Renderer for HSN/SAC
 */

namespace Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column\Renderer;

use Magento\Framework\Exception\NoSuchEntityException;

class HsnSac extends \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Column\DefaultRenderer
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * HsnSac constructor.
     * @param \Magetrend\PdfTemplates\Helper\Data $moduleHelper
     * @param \Magetrend\PdfTemplates\Model\Pdf\Element $element
     * @param \Magetrend\PdfTemplates\Model\Pdf\Decorator $decorator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        \Magetrend\PdfTemplates\Helper\Data $moduleHelper,
        \Magetrend\PdfTemplates\Model\Pdf\Element $element,
        \Magetrend\PdfTemplates\Model\Pdf\Decorator $decorator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($moduleHelper, $element, $decorator, $scopeConfig, $data);
    }

    /**
     * Get Row Value
     *
     * @return string
     */
    public function getRowValue()
    {
        $item = $this->getItem();
        $productId = $item->getProductId();

        try {
            $product = $this->productRepository->getById($productId);
            $hsnSac = $product->getData('hsn_sac');

            if ($hsnSac) {
                return (string)$hsnSac;
            }
        } catch (NoSuchEntityException $e) {
            // Product not found
        }

        return '-';
    }
}
