<?php
namespace Phonepe\PG\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Phonepe\PG\Helper\Data as PhonepeHelper;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'phonepe_pg';

    /**
     * @var PhonepeHelper
     */
    protected $helper;

    /**
     * @param PhonepeHelper $helper
     */
    public function __construct(PhonepeHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'displayMode'   => $this->helper->getDisplayMode(),
                    'checkoutJsUrl' => $this->helper->getCheckoutJsUrl(),
                ],
            ],
        ];
    }
}
