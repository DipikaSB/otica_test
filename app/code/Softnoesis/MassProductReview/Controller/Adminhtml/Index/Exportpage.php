<?php
/**
 * Softnoesis
 * Copyright(C) 05/2023 Softnoesis <ideveloper1990@gmail.com>
 * @package Softnoesis_MassProductReview
 * @copyright Copyright(C) 2015 Softnoesis (ideveloper1990@gmail.com)
 * @author Softnoesis <ideveloper1990@gmail.com>
 */
declare(strict_types=1);

namespace Softnoesis\MassProductReview\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Exportpage extends \Magento\Backend\App\Action
{

    protected $_fileFactory;
    protected $_reviewCollectionFactory;
    protected $directory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
    ) {
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR); // VAR Directory Path
        $this->_reviewCollectionFactory = $reviewCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $paramcollection = $this->getRequest()->getParams();
        if ($paramcollection['review_export_from'] !== '' && $paramcollection['review_export_to'] !== '') {
            $startDate = date("Y-m-d h:i:s", strtotime($paramcollection['review_export_from'])); // start date
            $endDate = date("Y-m-d h:i:s", strtotime($paramcollection['review_export_to']));
            $reviewsCollection = $this->_reviewCollectionFactory->create()
             ->addFieldToSelect('*')->addFieldToFilter('created_at', ['from'=>$startDate, 'to'=>$endDate])
             ->setDateOrder()
             ->addRateVotes();
        } else {
            $reviewsCollection = $this->_reviewCollectionFactory->create()
            ->addFieldToSelect('*')->addFieldToFilter('status_id', ['in' => $paramcollection['reviewstatus']])
            ->setDateOrder()
            ->addRateVotes();
        }
        $Collection = $reviewsCollection->getData();
        $name = date('m-d-Y-H-i-s');
        $filepath = 'export/product-review-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $columns = ['Store View', 'Product Id', 'Customer ID', 'Approval Status', 'Nick Name', 'Review Summary', 'Review Detail', 'Review Image', 'rating', 'Created Date'];
        $stream->writeCsv($columns);


        foreach ($reviewsCollection as $review) {
            $itemData = [];

            $itemData['Store View'] = $review->getStoreId();
            $itemData['Product Id'] = $review->getEntityPkValue();
            $itemData['Customer ID'] = $review->getCustomerId();
            $itemData['Approval Status'] = $review->getStatusId();
            $itemData['Nick Name'] = $review->getNickname();
            $itemData['Review Summary'] = $review->getTitle();
            $itemData['Review Detail'] = $review->getDetail();
            $itemData['Review Image'] = $review->getReviewImage();
            
            $itemData['rating'] = '';
            $itemData['Created Date'] = $review->getCreatedAt();

            $votes = $review->getRatingVotes();
            if ($votes && count($votes)) {
                foreach ($votes as $vote) {
                    $ratingCode = strtolower($vote->getRatingCode());
                    $ratingValue = $vote->getValue();

                    if (strpos($ratingCode, 'rating') !== false) {
                        $itemData['rating'] = $ratingValue;
                    }
                }
            }

            $stream->writeCsv($itemData);
        }
        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'pages-data-'.$name.'.csv';
        return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }
}
