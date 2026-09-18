<?php
/**
 * Class Index
 *
 * PHP version 7 & 8
 *
 * @category Sparsh
 * @package  Sparsh_SearchAutoComplete
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SearchAutoComplete\Controller\Ajax;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Search\Model\QueryFactory;
use \Sparsh\SearchAutoComplete\Block\Autocomplete;

/**
 * Class Index
 *
 * @category Sparsh
 * @package  Sparsh_SearchAutoComplete
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * QueryFactory
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    /**
     * AutoCompleteBlock
     *
     * @var \Sparsh\SearchAutoComplete\Block\Autocomplete
     */
    protected $autocompleteBlock;

    /**
     * Index constructor.
     *
     * @param Context      $context           context
     * @param QueryFactory $queryFactory      queryFactory
     * @param Autocomplete $autocompleteBlock autocompleteBlock
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        Autocomplete $autocompleteBlock
    ) {
        $this->queryFactory = $queryFactory;
        $this->autocompleteBlock = $autocompleteBlock;
        parent::__construct($context);
    }

    /**
     * Retrieve json of result data
     *
     * @return array|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $query = $this->queryFactory->get();

        $responseData = [];

        if ($query->getQueryText() != '') {
            $responseData['result'] = $this->autocompleteBlock->getResponseData();
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);

        return $resultJson;
    }
}
