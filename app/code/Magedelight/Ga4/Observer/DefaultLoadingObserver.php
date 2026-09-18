<?php
/**
 * @package Magedelight_Ga4 for Magento 2
 * @author MageDelight Team
 * @copyright Copyright (c) MageDelight (https://www.magedelight.com) owned by Krish TechnoLabs. All Rights reserved.
 */

namespace Magedelight\Ga4\Observer;

use Magedelight\Ga4\Helper\Data;
use Magento\Framework\Event\Observer;
use Magedelight\Ga4\Model\EventTrigger;

class DefaultLoadingObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magedelight\Ga4\Helper\Data
     */
    protected $mdhelper;

    /**
     * @var \Magedelight\Ga4\Model\EventTrigger
     */
    protected $eventTrigger;

    /**
     * Construct
     *
     * @param Data $mdhelper
     * @param EventTrigger $eventTrigger
     */
    public function __construct(Data $mdhelper, EventTrigger $eventTrigger)
    {
        $this->mdhelper = $mdhelper;
        $this->eventTrigger = $eventTrigger;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->mdhelper->isGTMStatus()) {
            $layout = $observer->getData('element_name');

            if ($layout != 'magedelight_ga4_top') {
                return $this;
            }

            $transport = $observer->getData('transport');
            $html = $transport->getOutput();

            $jsScript = $this->eventTrigger->gtagCode();
            $html = $jsScript . PHP_EOL . $html;

            $transport->setOutput($html);
        }
        return $this;
    }
}
