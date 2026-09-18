<?php

namespace Setblue\AllEmailCcBcc\Plugin\Model;

use Magento\Framework\Mail\TransportInterface as TransportSubject;
use Setblue\AllEmailCcBcc\Helper\Data;

class Transport
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Transport constructor
     *
     * @param Data $dataHelper
     */
    public function __construct(
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Add CC and BCC before sending email
     *
     * @param TransportSubject $subject
     * @return void
     */
    public function beforeSendMessage(TransportSubject $subject)
    {
        $storeId = (int) $this->dataHelper->getStoreId();

        if (!$this->dataHelper->isEnabled($storeId)) {
            return;
        }

        $message = $subject->getMessage();

        // Add CC Emails
        $ccEmails = $this->dataHelper->getCcTo($storeId);
        if (!empty($ccEmails)) {
            foreach ($ccEmails as $email) {
                $message->addCc(trim($email));
            }
        }

        // Add BCC Emails
        $bccEmails = $this->dataHelper->getBccTo($storeId);
        if (!empty($bccEmails)) {
            foreach ($bccEmails as $email) {
                $message->addBcc(trim($email));
            }
        }
    }
}