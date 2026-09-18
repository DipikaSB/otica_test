<?php
/**
 * Setblue 
 * @category    Setblue 
 * @copyright   Copyright (c) 2025 Setblue (http://www.setblue.com/) 
 * @Author: setblue.com
 * @Create Date: 2025-03-25 05:50:55
 */

namespace Setblue\Magicslider\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('setblue_magicslider'))
            ->addColumn(
                'magicslider_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Magicslider ID'
            )
            ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Title')
            ->addColumn('identifier', Table::TYPE_TEXT, 255, ['nullable' => true, 'default' => null])
            // ->addColumn('media_gallery', Table::TYPE_TEXT, '2M', [], 'Media Gallery')
            ->addColumn('config', Table::TYPE_TEXT, '2M', [], 'Config')
            ->addColumn('status', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Status')
            ->addColumn('created_time', Table::TYPE_DATETIME, null, ['nullable' => true, 'default' => null], 'Created Time')
            ->addColumn('update_time', Table::TYPE_DATETIME, null, ['nullable' => true, 'default' => null], 'Update Time')
            ->addIndex($installer->getIdxName('magicslider_id', ['magicslider_id']), ['magicslider_id'])
            ->setComment('Setblue Magicslider');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

}
