<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Setup;

use ClawRock\CustomerCoupon\Setup\InstallSchema;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use PHPUnit\Framework\TestCase;

class InstallSchemaTest extends TestCase
{
    /**
     * @var \Magento\Framework\Setup\SchemaSetupInterface
     */
    protected $setup;

    /**
     * @var \Magento\Framework\Setup\ModuleContextInterface
     */
    protected $context;

    /**
     * @var \ClawRock\CustomerCoupon\Setup\InstallSchema
     */
    protected $installSchema;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterfac
     */
    protected $connection;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->setup = $this->getMockBuilder(SchemaSetupInterface::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->context = $this->getMockBuilder(ModuleContextInterface::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->connection = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['addIndex', 'addForeignKey', 'addColumn']
        );

        $this->installSchema = $objectManager->getObject(
            InstallSchema::class,
            [
                'setup' => $this->setup,
                'context' => $this->context,
            ]
        );
    }

    public function testInstall()
    {
        $couponTable = 'salesrule_coupon';
        $salesRuleTable = 'salesrule';
        $customerEntity = 'customer_entity';
        $idxName = 'SALESRULE_COUPON_COUPON_CUSTOMER_ID';
        $fkName = 'SALESRULE_COUPON_COUPON_CUSTOMER_ID_CUSTOMER_ENTITY_ENTITY_ID';

        $this->setup->expects($this->once())
                    ->method('startSetup')
                    ->willReturnSelf();

        $this->setup->expects($this->once())
                    ->method('endSetup')
                    ->willReturnSelf();

        $this->setup->expects($this->once())
                    ->method('getIdxName')
                    ->with('salesrule_coupon', ['coupon_customer_id'])
                    ->willReturn($idxName);

        $this->setup->expects($this->exactly(3))
                    ->method('getTable')
                    ->willReturnMap(
                        [
                            [$couponTable, $couponTable],
                            [$salesRuleTable, $salesRuleTable],
                            [$customerEntity, $customerEntity],
                        ]
                    );
        $this->setup->expects($this->once())
                    ->method('getFkName')
                    ->with($couponTable, 'coupon_customer_id', $customerEntity, 'entity_id')
                    ->willReturn($fkName);

        $this->setup->expects($this->once())
                    ->method('getConnection')
                    ->willReturn($this->connection);

        $this->connection->expects($this->at(0))
                         ->method('addColumn')
                         ->with(
                             $salesRuleTable,
                             'apply_to_shipping_methods',
                             [
                                 'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                 'nullable' => true,
                                 'comment' => 'Apply free shipping to methods'
                             ]
                         )->willReturnSelf();

        $this->connection->expects($this->at(1))
                         ->method('addColumn')
                         ->with(
                             $couponTable,
                             'coupon_customer_id',
                             [
                                 'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                                 'nullable' => true,
                                 'unsigned' => true,
                                 'comment' => 'Customer id'
                             ]
                         )->willReturnSelf();

        $this->connection->expects($this->once())
                         ->method('addIndex')
                         ->with(
                             $couponTable,
                             $idxName,
                             ['coupon_customer_id']
                         )->willReturnSelf();

        $this->connection->expects($this->once())
                         ->method('addForeignKey')
                         ->with(
                             $fkName,
                             $couponTable,
                             'coupon_customer_id',
                             $customerEntity,
                             'entity_id',
                             \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
                         )->willReturnSelf();

        $this->installSchema->install($this->setup, $this->context);
    }
}
