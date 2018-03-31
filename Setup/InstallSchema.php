<?php

namespace ClawRock\CustomerCoupon\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param  SchemaSetupInterface   $setup
     * @param  ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();
        $couponTable = $setup->getTable('salesrule_coupon');
        $salesRuleTable = $setup->getTable('salesrule');

        $connection->addColumn(
            $salesRuleTable,
            'apply_to_shipping_methods',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Apply free shipping to methods'
            ]
        );

        $connection->addColumn(
            $couponTable,
            'coupon_customer_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'comment' => 'Customer id'
            ]
        );
        $connection->addIndex(
            $couponTable,
            $setup->getIdxName('salesrule_coupon', ['coupon_customer_id']),
            ['coupon_customer_id']
        );
        $connection->addForeignKey(
            $setup->getFkName('salesrule_coupon', 'coupon_customer_id', 'customer_entity', 'entity_id'),
            $couponTable,
            'coupon_customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        );

        $setup->endSetup();
    }
}
