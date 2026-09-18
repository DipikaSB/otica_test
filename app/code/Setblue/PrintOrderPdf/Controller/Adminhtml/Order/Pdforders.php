<?php
/**
 * @copyright Copyright (c) 2015 Setblue Limited (http://www.setblue.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Setblue\PrintOrderPdf\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class Pdforders extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Magento_Sales::sales_order';

    /**
     * @var string
     */
    protected $redirectUrl = '*/*/';

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Setblue\PrintOrderPdf\Model\Pdf\OrderPdfFactory
     */
    protected $orderPdfFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * Pdforders constructor.
     *
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \Magento\Ui\Component\MassAction\Filter                    $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory           $fileFactory
     * @param \Setblue\PrintOrderPdf\Model\Pdf\OrderPdfFactory           $orderPdfFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                $date
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Setblue\PrintOrderPdf\Model\Pdf\OrderPdfFactory $orderPdfFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->fileFactory = $fileFactory;
        $this->orderPdfFactory = $orderPdfFactory;
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * Print selected orders
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            if (!$collection->getSize()) {
                $this->messageManager->addErrorMessage(__('No orders selected.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath($this->redirectUrl);
            }

            $pdf = $this->orderPdfFactory->create()->getPdf($collection->getItems());

            $date = $this->date->date('Y-m-d_H-i-s');

            return $this->fileFactory->create(
                'orders_' . $date . '.pdf',
                ['type' => 'string', 'value' => $pdf->render(), 'rm' => true],
                DirectoryList::VAR_DIR,
                'application/pdf'
            );

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }
}
