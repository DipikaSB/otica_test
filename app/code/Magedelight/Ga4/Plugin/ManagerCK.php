<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Plugin;

use Magedelight\Ga4\Helper\Data as DataHelper;
use Magedelight\Ga4\Model\CookieData;
use Magento\Framework\App\FrontController;

class ManagerCK
{
    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magedelight\Ga4\Model\CookieData
     */
    protected $CookieData;

    /**
     * Construct
     *
     * @param DataHelper $dataHelper
     * @param CookieData $CookieData
     */
    public function __construct(DataHelper $dataHelper, CookieData $CookieData)
    {
        $this->dataHelper = $dataHelper;
        $this->CookieData = $CookieData;
    }

    /**
     * AfterDispatch
     *
     * @param FrontController $subject
     * @param result $result
     */
    public function afterDispatch(FrontController $subject, $result)
    {
        if ($this->dataHelper->isGTMStatus()) {
            $this->CookieData->setGa4Cookies();
        }
        return $result;
    }
}
