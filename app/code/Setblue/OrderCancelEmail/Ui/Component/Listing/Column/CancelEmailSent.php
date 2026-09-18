<?php
declare(strict_types=1);

namespace Setblue\OrderCancelEmail\Ui\Component\Listing\Column;

use Magento\Framework\Phrase;
use Magento\Ui\Component\Listing\Columns\Column;

class CancelEmailSent extends Column
{
    /**
     * Prepare datasource
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['cancel_email_sent'])) {
                $item['cancel_email_sent'] =
                    (int)$item['cancel_email_sent'] === 1
                        ? (string)__('Yes')
                        : (string)__('No');
            }
        }

        return $dataSource;
    }
}