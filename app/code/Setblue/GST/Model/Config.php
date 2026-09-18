<?php
namespace Setblue\GST\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\State as AppState;
use Magento\Sales\Model\AdminOrder\Create as AdminOrderCreate;

class Config
{
    const XPATH_STATUS  = 'gstnumber/general/enable';

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var AdminOrderCreate
     */
    protected $adminOrderCreate;

    /**
     * Config constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param AppState $appState
     * @param AdminOrderCreate $adminOrderCreate
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        AppState $appState,
        AdminOrderCreate $adminOrderCreate
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->appState = $appState;
        $this->adminOrderCreate = $adminOrderCreate;
    }
    /**
     * @return int
     */
    public function getStoreId()
    {
        if (!$this->storeId) {
            if ($this->appState->getAreaCode() == 'adminhtml') {
                $this->storeId = $this->adminOrderCreate->getQuote()->getStoreId();
            } else {
                $this->storeId = $this->storeManager->getStore()->getStoreId();
            }
        }

        return $this->storeId;
    }
    /**
     * @return mixed
     */
    public function getstatus()
    {
        $store = $this->getStoreId();
        return $this->scopeConfig->getValue(self::XPATH_STATUS, ScopeInterface::SCOPE_STORE, $store);
    }
}