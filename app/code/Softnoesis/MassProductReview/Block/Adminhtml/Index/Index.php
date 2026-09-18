<?php
/**
 * Softnoesis
 * Copyright(C) 05/2023 Softnoesis <ideveloper1990@gmail.com>
 * @package Softnoesis_MassProductReview
 * @copyright Copyright(C) 2015 Softnoesis (ideveloper1990@gmail.com)
 * @author Softnoesis <ideveloper1990@gmail.com>
 */
declare(strict_types=1);

namespace Softnoesis\MassProductReview\Block\Adminhtml\Index;

class Index extends \Magento\Backend\Block\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}
