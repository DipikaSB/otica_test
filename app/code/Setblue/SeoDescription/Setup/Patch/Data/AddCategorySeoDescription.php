<?php
namespace Setblue\SeoDescription\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class AddCategorySeoDescription implements DataPatchInterface
{
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();

        $eavSetup->addAttribute(
            Category::ENTITY,
            'seo_description',
            [
                'type' => 'text',
                'label' => 'SEO Description',
                'input' => 'textarea',
                'required' => false,
                'sort_order' => 120,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'General Information',
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'visible_on_front' => true,
                'note' => 'SEO Description shown at the bottom of category page.',
            ]
        );
    }

    public static function getDependencies() { return []; }

    public function getAliases() { return []; }
}

