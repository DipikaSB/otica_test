<?php
/**
 * Class Autocomplete
 *
 * PHP version 7 & 8
 *
 * @category Sparsh
 * @package  Sparsh_SearchAutoComplete
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SearchAutoComplete\Block;

use \Magento\Search\Helper\Data as SearchHelper;
use \Magento\Search\Model\AutocompleteInterface;
use \Magento\Search\Model\QueryFactory;
use \Magento\Catalog\Helper\Product;
use \Magento\Catalog\Block\Product\AbstractProduct;
use \Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use \Magento\Review\Model\ReviewFactory;
use \Magento\Framework\Url\Helper\Data as UrlHelper;
use \Magento\Framework\Data\Form\FormKey;

/**
 * Class Autocomplete
 *
 * @category Sparsh
 * @package  Sparsh_SearchAutoComplete
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Autocomplete extends \Magento\Framework\View\Element\Template
{
    /**
     * SearchAutoComplete Helper
     *
     * @var \Sparsh\SearchAutoComplete\Helper\Data
     */
    protected $helperData;

    /**
     * SearchHelper
     *
     * @var \Magento\Search\Helper\Data
     */
    protected $searchHelper;

    /**
     * AutoCompleteBlock
     *
     * @var \Magento\Search\Model\AutocompleteInterface;
     */
    protected $autocomplete;

    /**
     * QueryFactory
     *
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    /**
     * ProductHelper
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * AbstractProduct
     *
     * @var \Magento\Catalog\Block\Product\AbstractProduct
     */
    protected $abstractProduct;

    /**
     * LayerResolver
     *
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * UrlHelper
     *
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * FormKey
     *
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * CmsPageFactory
     *
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    protected $cmsCollectionFactory;

    /**
     * CmsPageHelper
     *
     * @var \Magento\Cms\Helper\Page
     */
    protected $cmsPageHelper;

    /**
     * CurrencyFactory
     *
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * CategoryHelper
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $categoryHelper;

    /**
     * CategoryFactory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Categorylist
     *
     * @var array
     */
    protected $categoryList = [];

    /**
     * Constructor
     *
     * @param \Sparsh\SearchAutoComplete\Helper\Data              $helperData           searchAutoCompleteHelper
     * @param \Magento\Framework\View\Element\Template\Context        $context              context
     * @param SearchHelper                                            $searchHelper         searchHelper
     * @param AutocompleteInterface                                   $autocomplete         autocomplete
     * @param QueryFactory                                            $queryFactory         queryFactory
     * @param Product                                                 $productHelper        productHelper
     * @param AbstractProduct                                         $abstractProduct      AbstractProduct
     * @param LayerResolver                                           $layerResolver        layerResolver
     * @param UrlHelper                                               $urlHelper            urlHelper
     * @param FormKey                                                 $formKey              formKey
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $cmsCollectionFactory cmsPageFactory
     * @param \Magento\Cms\Helper\Page                                $cmsPageHelper        cmsPageHelper
     * @param \Magento\Directory\Model\CurrencyFactory                $currencyFactory      currencyFactory
     * @param \Magento\Catalog\Helper\Category                        $categoryHelper       categoryHelper
     * @param \Magento\Catalog\Model\CategoryFactory                  $categoryFactory      categoryFactory
     * @param array                                                   $data                 data
     */
    public function __construct(
        \Sparsh\SearchAutoComplete\Helper\Data $helperData,
        \Magento\Framework\View\Element\Template\Context $context,
        SearchHelper $searchHelper,
        AutocompleteInterface $autocomplete,
        QueryFactory $queryFactory,
        Product $productHelper,
        AbstractProduct $abstractProduct,
        LayerResolver $layerResolver,
        UrlHelper $urlHelper,
        FormKey $formKey,
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $cmsCollectionFactory,
        \Magento\Cms\Helper\Page $cmsPageHelper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {

        $this->helperData = $helperData;
        $this->searchHelper = $searchHelper;
        $this->autocomplete = $autocomplete;
        $this->queryFactory = $queryFactory;
        $this->productHelper = $productHelper;
        $this->abstractProduct = $abstractProduct;
        $this->layerResolver = $layerResolver;
        $this->urlHelper = $urlHelper;
        $this->formKey = $formKey;
        $this->cmsCollectionFactory = $cmsCollectionFactory->create();
        $this->cmsPageHelper = $cmsPageHelper;
        $this->currencyFactory = $currencyFactory->create();
        $this->categoryHelper = $categoryHelper;
        $this->categoryFactory = $categoryFactory->create();
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current theme code
     *
     * @return string
     */
    public function getTheme()
    {
        $theme = $this->_design->getDesignTheme();

        return $theme->getCode();
    }

    /**
     * Retrieve search delay in milliseconds (1000 by default)
     *
     * @return int
     */
    public function getSearchDelayAutoComplete()
    {
        return $this->helperData->getSearchDelayAutoComplete();
    }

    /**
     * Retrieve search action url
     *
     * @return string
     */
    public function getSearchPageUrl()
    {
        return $this->getUrl("search_auto_complete/ajax/index");
    }

    /**
     * Retrieve search minimum characters default 3
     *
     * @return int
     */
    public function getSearchMinCharactersAutoComplete()
    {
        return $this->helperData->getSearchMinCharactersAutoComplete();
    }

    /**
     * Retrieve search no result text
     *
     * @return string
     */
    public function getSearchNoResultFoundText()
    {
        return $this->helperData->getSearchNoResultFoundText();
    }

    /**
     * Return Suggestion data Response
     *
     * @return array
     */
    public function getSuggestionResponseData()
    {
        $responseSuggData['code'] = 'suggest';
        $responseSuggData['data'] = [];

        $autocompleteData = $this->autocomplete->getItems();

        foreach ($autocompleteData as $itemData) {
            $itemData                   = $itemData->toArray();
            $itemData['url']            = $this->searchHelper->getResultUrl($itemData['title']);
            $responseSuggData['data'][] = $itemData;
        }

        return $responseSuggData;
    }

    /**
     * Return Product data Response
     *
     * @return array
     */
    public function getProductResponseData()
    {
        $responseProdData['code'] = 'product';
        $responseProdData['data'] = [];

        $productResultNumber = $this->helperData->getSearchMaximumItems();
        $productResultFields   = $this->helperData->getProductFieldsAsArray();
        $productResultFields[]   = 'url';
        $query                = $this->queryFactory->get();
        $queryText            = $query->getQueryText();

        $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);

        $productCollection = $this->layerResolver->get()
            ->getProductCollection()
            ->addAttributeToSelect('*')
            ->addSearchFilter($queryText);
        $productCollection->getSelect()->limit($productResultNumber);

        foreach ($productCollection as $product) {
            $responseProdData['data'][] = array_intersect_key(
                $this->getProductData($product),
                array_flip($productResultFields)
            );
        }

        $responseProdData['size'] = $productCollection->getSize();
        $responseProdData['url']  = ($productCollection->getSize() > 0) ?
            $this->searchHelper->getResultUrl($queryText) :
            '';

        $query->saveNumResults($responseProdData['size']);
        $query->saveIncrementalPopularity();
        return $responseProdData;
    }

    /**
     * Return Cms Page data Response
     *
     * @return array
     */
    public function getCmsResponseData()
    {
        $cmsResultNumber = $this->helperData->getCmsSearchMaximum();
        $responseCmsData['code'] = 'cms';
        $responseCmsData['data'] = [];

        $query                = $this->queryFactory->get();
        $queryText            = $query->getQueryText();
        $collection = $this->cmsCollectionFactory;
        $collection->addFieldToFilter(
            ['title', 'content'],
            [
                ['like' => '%'.$queryText.'%'],
                ['like' => '%'.$queryText.'%']
            ]
        )->setPageSize($cmsResultNumber);
        foreach ($collection as $page) {
            $CMSPageURL = $this->cmsPageHelper->getPageUrl($page->getId());
            $responseCmsData['data'][] = [
                "title" => $page->getTitle(),
                "url" => $CMSPageURL,
                "content" => $this->cropDescription($page->getContent())
            ];
        }

        $responseCmsData['size'] = $collection->getSize();
        $responseCmsData['url']  = ($collection->getSize() > 0) ?
            $this->searchHelper->getResultUrl($queryText) :
            '';

        $query->saveNumResults($responseCmsData['size']);
        $query->saveIncrementalPopularity();
        return $responseCmsData;
    }

    /**
     * Return Category data Response
     *
     * @return array
     */
    public function getCategoryResponseData()
    {
        $responseCatData['code'] = 'category';
        $responseCatData['data'] = [];

        $categoryResultNumber = $this->helperData->getCategorySearchMaximum();

        $query                = $this->queryFactory->get();
        $queryText            = $query->getQueryText();

        $categoryHelper = $this->categoryHelper;
        $categoryCollection = $categoryHelper->getStoreCategories(false, true, true)
            ->addAttributeToSelect('parent_id')
            ->addFieldToFilter('name', ['like' => '%'.$queryText.'%']);
        $categoryCollection->getSelect()->limit($categoryResultNumber);

        foreach ($categoryCollection->getData() as $category) {
            $this->categoryList[$category['entity_id']] = [
                "id" => $category['entity_id'],
                "name" => $category['name'],
                "path" => $this->_storeManager->getStore()->getBaseUrl().$category['request_path'],
                "parent_id" => $category['parent_id']
            ];
            if ($category['parent_id'] > 2 && $category['parent_id'] != null) {
                $this->getCatParentName($category['entity_id'], $category['parent_id']);
            } else {
                $this->categoryList[$category['entity_id']]['parent_name'] = '';
            }

        }
        $responseCatData['data'] = $this->categoryList;
        $responseCatData['size'] = $categoryCollection->getSize();
        $query->saveNumResults($responseCatData['size']);
        $query->saveIncrementalPopularity();
        return $responseCatData;
    }

    /**
     * Return Parent Name for category
     *
     * @param int         $catId      categoryId
     * @param int         $parentId   parentCategoryId
     * @param string|null $parentName parentName
     *
     * @return string
     */
    public function getCatParentName($catId, $parentId, $parentName = null)
    {
        $parentCategory = $this->categoryFactory->load($parentId)->getData();
        $parentName = $parentName == null ?
            $parentCategory['name'] :
            $parentCategory['name'] . ' > ' . $parentName;
        $this->categoryList[$catId]['parent_name'] = $parentName;
        if ($parentCategory['parent_id'] != null && $parentCategory['parent_id'] > 2) {
            $this->getCatParentName($catId, $parentCategory['parent_id'], $parentName);
        }
        return $parentName;
    }

    /**
     * Retrieve suggested, category, product, cms data
     *
     * @return array
     */
    public function getResponseData()
    {
        $data = [];

        /* load product data if product search is enable */
        if ($this->helperData->getIsProductSearchEnable()) {
            $data[] = $this->getProductResponseData();
        }

        /* load popular suggestions if popular suggestions is enable */
        if ($this->helperData->getIsPopularSuggestionsEnable()) {
            $data[] = $this->getSuggestionResponseData();
        }

        /* load cms data if cms search is enable */
        if ($this->helperData->getIsCmsSearchEnable()) {
            $data[] = $this->getCmsResponseData();
        }

        /* load category data if category search is enable */
        if ($this->helperData->getIsCategorySearchEnable()) {
            $data[] = $this->getCategoryResponseData();
        }

        return $data;
    }

    /**
     * Retrieve product data
     *
     * @param \Magento\Catalog\Model\Product $product product
     *
     * @return array
     */
    public function getProductData($product)
    {
        $price = $product->getFormattedPrice();
        if ($product->getTypeId() == 'bundle' || $product->getTypeId() == 'grouped') {
            $price = '';
            if (!empty($product->getMinimalPrice())) {
                $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
                $currency = $this->currencyFactory->load($currencyCode);
                $price .= '<span class="price-label">As low as</span>&nbsp;&nbsp;';
                $price .= '<span class="price">'.$currency->getCurrencySymbol().number_format($product->getMinimalPrice(), 2).'</span>';
            }
        }

        $data = [
            'name'              => $product->getName(),
            'sku'               => $product->getSku(),
            'image'             => $this->productHelper->getSmallImageUrl($product),
            'reviews_rating'    => $this->abstractProduct->getReviewsSummaryHtml($product),
            'short_description' => $this->cropDescription($product->getShortDescription()),
            'description'       => $this->cropDescription($product->getDescription()),
            'price'             => $price,
            'add_to_cart'       => $this->getAddToCartButtonData($product),
            'url'               => $product->getProductUrl()
        ];

        return $data;
    }

    /**
     * Generate Add to cart button data
     *
     * @param \Magento\Catalog\Model\Product $product product
     *
     * @return array
     */
    public function getAddToCartButtonData($product)
    {
        $formUrl             = $this->abstractProduct->getAddToCartUrl($product);
        $productId           = $product->getEntityId();
        $paramNameUrlEncoded = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
        $urlEncoded          = $this->urlHelper->getEncodedUrl($formUrl);
        $formKey             = $this->formKey->getFormKey();

        $addToCartData = [
            'formUrl'             => $formUrl,
            'productId'           => $productId,
            'paramNameUrlEncoded' => $paramNameUrlEncoded,
            'urlEncoded'          => $urlEncoded,
            'formKey'             => $formKey
        ];

        return $addToCartData;
    }

    /**
     * Fetch cropped description
     *
     * @param string $html html
     *
     * @return string
     */
    public function cropDescription($html)
    {
        $string = strip_tags((string)$html);

        if ($this->helperData->getProductDescMaxWords()) {
            $string = (str_word_count($string) > $this->helperData->getProductDescMaxWords()) ?
                implode(' ', array_slice(str_word_count($string, 1), 0, $this->helperData->getProductDescMaxWords())) . '...' :
                $html;
        } else {
            $string = (strlen($string) > 50) ? $this->string->substr($string, 0, 50) . '...' : $html;
        }

        return $string;
    }
}
