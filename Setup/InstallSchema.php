<?php
namespace Kash\Gateway\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{

    public function install( SchemaSetupInterface $setup, ModuleContextInterface $context ) 
    {
        $installer = $setup;

        $installer->startSetup();

        $installer->getConnection()
            ->addColumn(
                $installer->getTable('sales_order_payment'), 'x_gateway_reference', array(
                'type'    => Table::TYPE_TEXT,
                'comment' => 'Gateway Reference',
                'length'  => '255'
                )
            );

        $installer->endSetup();
    }
}