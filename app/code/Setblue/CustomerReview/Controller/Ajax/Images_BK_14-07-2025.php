<?php
namespace Setblue\CustomerReview\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

class Images extends Action
{
    protected $resultJsonFactory;
    protected $pageFactory;
    protected $productRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $pageFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->pageFactory = $pageFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('product_id');
        $productId = 16722 ;
        if (!$productId) {
            return $this->resultJsonFactory->create()->setData(['output' => '<p>No product ID provided.</p>']);
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            return $this->resultJsonFactory->create()->setData(['output' => '<p>Invalid product.</p>']);
        }

        $layout = $this->pageFactory->create()->getLayout();

        $block = $layout->createBlock(\Setblue\CustomerReview\Block\Review\Images::class)
            ->setProduct($product)
            ->setData('product_id', $productId)
    		->setTemplate('Setblue_CustomerReview::review/images_ajax.phtml');


        $html = $block->toHtml();

        $result = $this->resultJsonFactory->create();
        return $result->setData(['output' => $html]);
    }
}
