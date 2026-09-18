<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Setblue\CustomerReview\Block\Adminhtml\Edit;

/**
 * Adminhtml Review Edit Form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Review data
     *
     * @var \Magento\Review\Helper\Data
     */
    protected $_reviewData = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Catalog product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Core system store model
     *
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Customer\APi\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Review\Helper\Data $reviewData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Review\Helper\Data $reviewData,
        array $data = []
    ) {
        $this->_reviewData = $reviewData;
        $this->customerRepository = $customerRepository;
        $this->_productFactory = $productFactory;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare edit review form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $review = $this->_coreRegistry->registry('review_data');
        $product = $this->_productFactory->create()->load($review->getEntityPkValue());

        $formActionParams = [
            'id' => $this->getRequest()->getParam('id'),
            'ret' => $this->_coreRegistry->registry('ret')
        ];
        if ($this->getRequest()->getParam('productId')) {
            $formActionParams['productId'] = $this->getRequest()->getParam('productId');
        }
        if ($this->getRequest()->getParam('customerId')) {
            $formActionParams['customerId'] = $this->getRequest()->getParam('customerId');
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl(
                        'review/*/save',
                        $formActionParams
                    ),
                    'method' => 'post',
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            'review_details',
            ['legend' => __('Review Details 222'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'product_name',
            'note',
            [
                'label' => __('Product'),
                'text' => '<a href="' . $this->getUrl(
                    'catalog/product/edit',
                    ['id' => $product->getId()]
                ) . '" onclick="this.target=\'blank\'">' . $this->escapeHtml(
                    $product->getName()
                ) . '</a>'
            ]
        );

        try {
            $customer = $this->customerRepository->getById($review->getCustomerId());
            $customerText = __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                $this->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'review']),
                $this->escapeHtml($customer->getFirstname()),
                $this->escapeHtml($customer->getLastname()),
                $this->escapeHtml($customer->getEmail())
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customerText = ($review->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID)
                ? __('Administrator') : __('Guest');
        }

        $fieldset->addField('customer', 'note', ['label' => __('Author'), 'text' => $customerText]);

        $fieldset->addField(
            'summary-rating',
            'note',
            [
                'label' => __('Summary Rating'),
                'text' => $this->getLayout()->createBlock(
                    \Magento\Review\Block\Adminhtml\Rating\Summary::class
                )->toHtml()
            ]
        );

        $fieldset->addField(
            'detailed-rating',
            'note',
            [
                'label' => __('Detailed Rating'),
                'required' => true,
                'text' => '<div id="rating_detail">' . $this->getLayout()->createBlock(
                    \Magento\Review\Block\Adminhtml\Rating\Detailed::class
                )->toHtml() . '</div>'
            ]
        );

        $fieldset->addField(
            'status_id',
            'select',
            [
                'label' => __('Status'),
                'required' => true,
                'name' => 'status_id',
                'values' => $this->_reviewData->getReviewStatusesOptionArray()
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->hasSingleStore()) {
            $field = $fieldset->addField(
                'select_stores',
                'multiselect',
                [
                    'label' => __('Visibility'),
                    'required' => true,
                    'name' => 'stores[]',
                    'values' => $this->_systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'select_stores',
                'hidden',
                ['name' => 'stores[]', 'value' => $review->getStores()]
            );
        }
        $review->setSelectStores($review->getStores());

        $fieldset->addField(
            'nickname',
            'text',
            ['label' => __('Nickname'), 'required' => true, 'name' => 'nickname']
        );

        $fieldset->addField(
            'title',
            'text',
            ['label' => __('Summary of Review'), 'required' => true, 'name' => 'title']
        );

        $fieldset->addField(
            'detail',
            'textarea',
            ['label' => __('Review'), 'required' => true, 'name' => 'detail', 'style' => 'height:24em;']
        );
        $mediaHtml = '';
        $reviewImage = $review->getReviewImage(); // Assumes this is a relative path like "review/image/xyz.jpg"

        if (!empty($reviewImage)) {
            $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $reviewMediaUrl = $mediaUrl . $reviewImage;
            $extension = strtolower(pathinfo($reviewImage, PATHINFO_EXTENSION));

            $allowedExtensions = [
                'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg',
                'mp4', 'mov', 'avi', 'flv', 'wmv', 'mkv', 'webm', 'm4v', '3gp'
            ];

            if (in_array($extension, $allowedExtensions)) {
                if (in_array($extension, ['mp4', 'mov', 'avi', 'flv', 'wmv', 'mkv', 'webm', 'm4v', '3gp'])) {
                    $mediaHtml = '<a href="' . $reviewMediaUrl . '" target="_blank">
                                    <video width="320" height="240" controls playsinline>
                                        <source src="' . $reviewMediaUrl . '" type="video/' . $extension . '">
                                        Your browser does not support the video tag.
                                    </video>
                                  </a>';
                } else {
                    $mediaHtml = '<a href="' . $reviewMediaUrl . '" target="_blank">
                                    <img src="' . $reviewMediaUrl . '" alt="Review Media" style="max-width: 320px; max-height: 240px;" />
                                  </a>';
                }
            }
        }

        $fieldset->addField(
            'review_media',
            'note',
            [
                'label' => __('Review Media'),
                'text'  => $mediaHtml
            ]
        );

        $form->setUseContainer(true);
        $form->setValues($review->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
