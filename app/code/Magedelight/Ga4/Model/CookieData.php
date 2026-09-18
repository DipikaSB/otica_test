<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Model;

class CookieData
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieHandle;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $interfaceSession;

    /**
     * Construct
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
     * @param \Magedelight\Ga4\Helper\Data $dataHelper
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieCollectionFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieHandle
     * @param \Magento\Framework\Session\SessionManagerInterface $interfaceSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
        \Magedelight\Ga4\Helper\Data $dataHelper,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieCollectionFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieHandle,
        \Magento\Framework\Session\SessionManagerInterface $interfaceSession
    ) {
        $this->cookieCollectionFactory = $cookieCollectionFactory;
        $this->cookieHandle = $cookieHandle;
        $this->interfaceSession = $interfaceSession;
        $this->customerSession = $customerSession;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->dataHelper = $dataHelper;
    }

    /**
     * GetStoreCookies
     */
    public function getStoreCookies()
    {
        $cookies = ["customerId","customerGroup"];
        return $cookies;
    }

    /**
     * SetGa4Cookies
     */
    public function setGa4Cookies()
    {
        $cookieStatus = $this->dataHelper->getCookiesSetting();
        $cookieContent = $this->cookieCollectionFactory->createPublicCookieMetadata()
            ->setDurationOneYear()
            ->setPath('/')
            ->setDomain($this->interfaceSession->getCookieDomain())->setSecure($cookieStatus)->setHttpOnly(false);
        if ($this->dataHelper->NotLoggedInCustomerIdEnabled() &&
            $this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomerId();
            $this->cookieHandle->setPublicCookie("customerId", $customerId, $cookieContent);
        } else {
            $this->cookieHandle->deleteCookie("customerGroup", $cookieContent);
        }

        if ($this->dataHelper->NotLoggedInCustomerIdEnabled()) {
            $customerGroup = 'NOT LOGGED IN';
            if ($this->customerSession->isLoggedIn()) {
                $customerGroupId = $this->customerSession->getCustomerGroupId();
                $groupObj = $this->customerGroupRepository->getById($customerGroupId);
                $customerGroup = $groupObj->getCode();
            }
            $this->cookieHandle->setPublicCookie("customerId", $customerGroup, $cookieContent);
        } else {
            $this->cookieHandle->deleteCookie("customerGroup", $cookieContent);
        }
    }
}
