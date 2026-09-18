<?php

namespace Setblue\OticaSku\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddOticaSkuToOrderItem implements SchemaPatchInterface
{
    private $schemaSetup;

    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply()
    {
        $this->schemaSetup->startSetup();

        if (!$this->schemaSetup->tableExists('sales_order_item')) {
            return;
        }

        $this->schemaSetup->getConnection()->addColumn(
            $this->schemaSetup->getTable('sales_order_item'),
            'otica_sku',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Otica SKU'
            ]
        );

        $this->schemaSetup->endSetup();
    }

    public static function getDependencies() { return []; }
    public function getAliases() { return []; }
}
