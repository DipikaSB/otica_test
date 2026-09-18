<?php

namespace Setblue\AllEmailCcBcc\Plugin\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Setblue\AllEmailCcBcc\Helper\Data;

class TransportBuilderPlugin
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * TransportBuilderPlugin constructor.
     *
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Add CC and BCC before getting transport
     *
     * @param TransportBuilder $subject
     * @return TransportBuilder
     */
    public function beforeGetTransport(TransportBuilder $subject)
    {
        $storeId = (int) $this->dataHelper->getStoreId();

        if (!$this->dataHelper->isEnabled($storeId)) {
            return [$subject];
        }

        // Add CC Emails
        $ccEmails = $this->dataHelper->getCcTo($storeId);
        if (!empty($ccEmails)) {
            foreach ($ccEmails as $email) {
                $subject->addCc(trim($email));
            }
        }

        // Add BCC Emails
        $bccEmails = $this->dataHelper->getBccTo($storeId);
        if (!empty($bccEmails)) {
            foreach ($bccEmails as $email) {
                $subject->addBcc(trim($email));
            }
        }

        return [$subject];
    }
}
