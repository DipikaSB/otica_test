<?php

namespace Setblue\ContactUsGrid\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class CustomColumn extends Column
{
    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $columnName = $this->getData('name');
        $requestParams = $this->getContext()->getRequestParam('columns');

        if ($columnName && $requestParams) {
            $isVisible = isset($requestParams[$columnName]['visible'])
                ? (bool)$requestParams[$columnName]['visible']
                : false;
            $this->setData('config/visible', $isVisible);
        }
        parent::prepare();
    }
}
