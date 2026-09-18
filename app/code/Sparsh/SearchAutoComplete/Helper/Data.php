<?php
/**
 * Class Data
 *
 * PHP version 7 & 8
 *
 * @category Sparsh
 * @package  Sparsh_SearchAutoComplete
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\SearchAutoComplete\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @category Sparsh
 * @package  Sparsh_SearchAutoComplete
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Retrieve search delay default 1000ms
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getSearchDelayAutoComplete($storeId = null)
    {
        $searchDelayPath = 'sparsh_search_auto_complete/general/search_delay';
        return (int)$this->scopeConfig->getValue(
            $searchDelayPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ? (int)$this->scopeConfig->getValue(
            $searchDelayPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) : 1000;
    }

    /**
     * Retrieve search enable
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getIsSearchEnable($storeId = null)
    {
        $searchEnable = 'sparsh_search_auto_complete/general/enable';
        return $this->scopeConfig->getValue(
            $searchEnable,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve search no result text
     *
     * @param int|null $storeId storeId
     *
     * @return string
     */
    public function getSearchNoResultFoundText($storeId = null)
    {
        $searchNoResult = 'sparsh_search_auto_complete/general/search_no_result';
        return $this->scopeConfig->getValue(
            $searchNoResult,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ? $this->scopeConfig->getValue(
            $searchNoResult,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) : 'No Results found.';
    }

    /**
     * Retrieve product search enable
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getIsProductSearchEnable($storeId = null)
    {
        $productEnable = 'sparsh_search_auto_complete/product_settings/product_search_enable';
        return $this->scopeConfig->getValue(
            $productEnable,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve search minimum characters default 3
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getSearchMinCharactersAutoComplete($storeId = null)
    {
        $searchMinChars = 'sparsh_search_auto_complete/general/search_minimum_characters';
        return $this->scopeConfig->getValue(
            $searchMinChars,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ? $this->scopeConfig->getValue(
            $searchMinChars,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) : 3;
    }

    /**
     * Retrieve search maximum items default 5
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getSearchMaximumItems($storeId = null)
    {
        $searchMaxItems = 'sparsh_search_auto_complete/product_settings/product_search_maximum_items';
        return $this->scopeConfig->getValue(
            $searchMaxItems,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ? $this->scopeConfig->getValue(
            $searchMaxItems,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) : 5;
    }

    /**
     * Retrieve comma-separated product display fields
     *
     * @param int|null $storeId storeId
     *
     * @return string
     */
    public function getProductFields($storeId = null)
    {
        $searchDisplayFields = 'sparsh_search_auto_complete/product_settings/product_search_display_fields';
        return $this->scopeConfig->getValue(
            $searchDisplayFields,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve list of product display fields
     *
     * @param int|null $storeId storeId
     *
     * @return array
     */
    public function getProductFieldsAsArray($storeId = null)
    {
        return explode(',', $this->getProductFields($storeId));
    }

    /**
     * Retrieve product description maximum words
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getProductDescMaxWords($storeId = null)
    {
        $searchDescMaxWords = 'sparsh_search_auto_complete/product_settings/product_search_maximum_words_description';
        return (int)$this->scopeConfig->getValue(
            $searchDescMaxWords,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve popular suggestions enable
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getIsPopularSuggestionsEnable($storeId = null)
    {
        $searchPopularSugg = 'sparsh_search_auto_complete/popular_suggestion_settings/popular_suggestion_enable';
        return (int)$this->scopeConfig->getValue(
            $searchPopularSugg,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve category search enable
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getIsCategorySearchEnable($storeId = null)
    {
        $searchCategoryEnable = 'sparsh_search_auto_complete/category_search_settings/category_search_enable';
        return $this->scopeConfig->getValue(
            $searchCategoryEnable,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve category search Maximum allowed default 3
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getCategorySearchMaximum($storeId = null)
    {
        $searchMaxCat = 'sparsh_search_auto_complete/category_search_settings/category_search_maximum';
        return $this->scopeConfig->getValue(
            $searchMaxCat,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ? $this->scopeConfig->getValue(
            $searchMaxCat,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) : 3;
    }

    /**
     * Retrieve cms search enable
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getIsCmsSearchEnable($storeId = null)
    {
        $searchCmsEnable = 'sparsh_search_auto_complete/cms_search_settings/cms_search_enable';
        return $this->scopeConfig->getValue(
            $searchCmsEnable,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Retrieve cms search Maximum allowed default 3
     *
     * @param int|null $storeId storeId
     *
     * @return int
     */
    public function getCmsSearchMaximum($storeId = null)
    {
        $searchMaxCms = 'sparsh_search_auto_complete/cms_search_settings/cms_search_maximum';
        return $this->scopeConfig->getValue(
            $searchMaxCms,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ? $this->scopeConfig->getValue(
            $searchMaxCms,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) : 3;
    }
}
