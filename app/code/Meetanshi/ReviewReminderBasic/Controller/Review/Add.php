<?php

namespace Meetanshi\ReviewReminderBasic\Controller\Review;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Controller for displaying the dedicated review form page.
 */
class Add extends Action implements HttpGetActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Add constructor.
     *
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        PageFactory $resultPageFactory
    ) {
        $this->productRepository = $productRepository;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute action - load product and render review form page.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('product_id');

        if (!$productId) {
            $this->messageManager->addErrorMessage(__('Product not found.'));
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('The requested product is no longer available.'));
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            __('Write a Review for %1', $product->getName())
        );

        return $resultPage;
    }
}
