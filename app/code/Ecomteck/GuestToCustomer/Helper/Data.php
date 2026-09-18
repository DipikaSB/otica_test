<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_GuestToCustomer
 * @copyright   Copyright (c) 2019 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */

namespace Ecomteck\GuestToCustomer\Helper;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;

/**
 * Class Data
 * @package Ecomteck\GuestToCustomer\Helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_ACTIVE = 'ecguesttocustomer/general/active';
    const XML_CUSTOMER_DASHBOARD = 'ecguesttocustomer/general/customer_dashboard';
    const XML_AUTO_CONVERT_GUEST_TO_CUSTOMER = 'ecguesttocustomer/general/auto_convert_guest_to_customer';
    const XML_ASSIGN_ORDER_ADDRESS_TO_CUSTOMER = 'ecguesttocustomer/general/assign_order_address_to_customer';
    const XML_ASSIGN_CUSTOMER_GROUP = 'ecguesttocustomer/general/assign_customer_group';
    const XML_CUSTOMER_ALREADY_EXISTS = 'ecguesttocustomer/general/merge_customer_already_exists';

    const XML_MERGE_CUSTOMER_GROUP = 'ecguesttocustomer/merge/group';
    const XML_MERGE_CUSTOMER_NAME = 'ecguesttocustomer/merge/name';
    const XML_MERGE_CUSTOMER_DOB = 'ecguesttocustomer/merge/dob';
    const XML_MERGE_CUSTOMER_GENDER = 'ecguesttocustomer/merge/gender';
    const XML_MERGE_CUSTOMER_TAXVAT = 'ecguesttocustomer/merge/taxvat';

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * @var Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Api\RegionInterfaceFactory
     */
    protected $regionDataFactory;
    /**
     * @var OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @param Context $context
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param OrderCustomerManagementInterface $orderCustomerService
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        OrderCustomerManagementInterface $orderCustomerService
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->_storeManager = $storeManager;
        $this->orderCustomerService = $orderCustomerService;
        $this->regionDataFactory = $regionDataFactory;
        parent::__construct($context);
    }

    /**
     * Whether is active
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isConfigEnabled(self::XML_PATH_ACTIVE);
    }

    /**
     * Customer dashboard active
     *
     * @return bool
     */
    public function isEnabledCustomerDashboard()
    {
        return $this->isEnabled() && $this->isConfigEnabled(self::XML_CUSTOMER_DASHBOARD);
    }

    /**
     * Automatically add order to existing customer with same email address.
     * @return bool
     */
    public function isMergeIfCustomerAlreadyExists()
    {
        return $this->isConfigEnabled(self::XML_CUSTOMER_ALREADY_EXISTS);
    }

    /**
     * @param $customerId int
     * @param $orderId int
     * @return $this
     */
    public function dispatchCustomerOrderLinkEvent($customerId, $orderId)
    {
        $this->_eventManager->dispatch('ecomteck_guest_to_customer_save', [
            'customer_id' => $customerId,
            'order_id' => $orderId, //incrementId
            'increment_id' => $orderId //$incrementId
        ]);

        return $this;
    }

    /**
     * @param $order OrderInterface
     * @param $customer CustomerInterface
     */
    public function setCustomerData(OrderInterface $order, CustomerInterface $customer)
    {
        $order->setCustomerIsGuest(0);
        $order->setCustomerId($customer->getId());

        if ($this->isConfigEnabled(self::XML_MERGE_CUSTOMER_GROUP)) {
            $order->setCustomerGroupId($customer->getGroupId());
        }

        if ($this->isConfigEnabled(self::XML_MERGE_CUSTOMER_NAME)) {
            $order->setCustomerPrefix($customer->getPrefix());

            $order->setCustomerFirstname($customer->getFirstname());
            $order->setCustomerLastname($customer->getLastname());
            $order->setCustomerMiddlename($customer->getMiddlename());

            $order->setCustomerSuffix($customer->getSuffix());
        }

        if ($this->isConfigEnabled(self::XML_MERGE_CUSTOMER_DOB)) {
            $order->setCustomerDob($customer->getDob());
        }

        if ($this->isConfigEnabled(self::XML_MERGE_CUSTOMER_GENDER)) {
            $order->setCustomerGender($customer->getGender());
        }

        if ($this->isConfigEnabled(self::XML_MERGE_CUSTOMER_TAXVAT)) {
            $order->setCustomerTaxvat($customer->getTaxvat());
        }
    }

    /**
     * @param int $orderId
     *
     * @return Magento\Customer\Api\CustomerRepositoryInterface | boolean
     */
    public function convertGuestToCustomer($orderId){
        $customer = $this->orderCustomerService->create($orderId);
        return $customer;
    }

    /**
     * @param $xmlPath string
     *
     * @return boolean
     */
    protected function isConfigEnabled($xmlPath)
    {
        return $this->scopeConfig->isSetFlag(
            $xmlPath,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function isAutoConvertGuestToCustomer(){
        return $this->getConfig(self::XML_AUTO_CONVERT_GUEST_TO_CUSTOMER);
    }

    /**
     * @return mixed
     */
    public function isAssignOrderAddressToCustomer(){
        return $this->getConfig(self::XML_ASSIGN_ORDER_ADDRESS_TO_CUSTOMER);
    }

    /**
     * @return mixed
     */
    public function getAssignCustomerGroup(){
        return $this->getConfig(self::XML_ASSIGN_CUSTOMER_GROUP);
    }

    /**
     * @param $key
     * @param null $store
     * @param string $section
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig($key, $store = null, $section = '')
    {
        $store = $this->_storeManager->getStore($store);
        if($section){
            $section .= "/";
        }
        $result = $this->scopeConfig->getValue(
            $section.$key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
        return $result;
    }

    /**
     * @param int $customerId
     * @param Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return void
     */
    public function saveCustomerAddress($customerId, $order)
    {
        $address = $this->addressDataFactory->create();
        $region = $this->regionDataFactory->create();
        if($customerId && $order && $order->getEntityId()){
            $billingAddress = $order->getBillingAddress();
            if($billingAddress){
                $firstName = $billingAddress->getFirstname();
                $lastName = $billingAddress->getLastname();
                $regionName = $billingAddress->getRegion();
                $region->setRegion($regionName);
                $countryId = $billingAddress->getCountryId();
                $regionId = $billingAddress->getRegionId();
                $city = $billingAddress->getCity();
                $postcode = $billingAddress->getPostcode();
                $street = $billingAddress->getStreet();
                $telephone = $billingAddress->getTelephone();

                $address->setFirstname($firstName)
                        ->setLastname($lastName)
                        ->setCountryId($countryId)
                        ->setRegionId($regionId)
                        ->setRegion($region)
                        ->setCity($city)
                        ->setPostcode($postcode)
                        ->setCustomerId($customerId)
                        ->setStreet($street)
                        ->setTelephone($telephone)
                        ->setIsDefaultBilling(true)
                        ->setIsDefaultShipping(true);

                $this->addressRepository->save($address);
            }
        }
    }

}
