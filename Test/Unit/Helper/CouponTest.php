<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Helper;

use ClawRock\CustomerCoupon\Helper\Coupon as CouponHelper;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use PHPUnit\Framework\TestCase;

class CouponTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Helper\Coupon
     */
    protected $helper;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $coupon;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->customerFactory = $this->getMockBuilder(CustomerFactory::class)
                                      ->setMethods(['create'])
                                      ->disableOriginalConstructor()
                                      ->getMock();

        $this->customer = $this->getMockBuilder(Customer::class)
                               ->setMethods(['getEmail', 'load', 'getId', 'getSharingConfig'])
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->couponFactory = $this->getMockBuilder(CouponFactory::class)
                                    ->setMethods(['create'])
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->coupon = $this->getMockBuilder(Coupon::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->ruleFactory = $this->getMockBuilder(RuleFactory::class)
                                  ->setMethods(['create'])
                                  ->disableOriginalConstructor()
                                  ->getMock();

        $this->rule = $this->getMockBuilder(Rule::class)
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->helper = $objectManager->getObject(
            CouponHelper::class,
            [
                'context'  => $contextMock,
                'customerFactory' => $this->customerFactory,
                'resourceConnection' => $this->resourceConnection,
                'couponFactory' => $this->couponFactory,
                'ruleFactory' => $this->ruleFactory
            ]
        );
    }

    public function prepareData($factory, $model, $modelId)
    {
        $factory->expects($this->once())
                ->method('create')
                ->willReturn($model);

        $model->expects($this->once())
              ->method('load')
              ->with($modelId)
              ->willReturnSelf();

        $model->expects($this->once())
              ->method('getId')
              ->willReturn($modelId);
    }

    public function testLoadRule()
    {
        $this->prepareData($this->ruleFactory, $this->rule, 1);

        $this->assertInstanceOf(
            Rule::class,
            $this->helper->loadRule(1, $this->ruleFactory, "Rule doesn't exists")
        );
    }

    public function testLoadCoupon()
    {
        $this->prepareData($this->couponFactory, $this->coupon, 1);
        $this->assertInstanceOf(
            Coupon::class,
            $this->helper->loadCoupon(1, $this->couponFactory, "Coupon doesn't exists")
        );
    }

    public function testModelException()
    {
        $couponId = 1;

        $this->couponFactory->expects($this->once())
                            ->method('create')
                            ->willReturn($this->coupon);

        $this->coupon->expects($this->once())
                     ->method('load')
                     ->with($couponId)
                     ->willReturnSelf();

        $this->coupon->expects($this->once())
                     ->method('getId')
                     ->willReturn(null);
        $this->expectException(LocalizedException::class);
        $this->helper->loadCoupon($couponId, $this->couponFactory, "Coupon doesn't exists");
    }

    public function testGetCustomerEmail()
    {
        $customerEmail = 'test@example.com';
        $this->prepareData($this->customerFactory, $this->customer, 1);
        $this->customer->expects($this->once())
                       ->method('getEmail')
                       ->willReturn($customerEmail);

        $this->assertEquals($customerEmail, $this->helper->getCustomerEmail(1));
    }

    public function preapreResourceConnection($return = null)
    {
        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $selectMock->expects($this->any())->method('from')->willReturnSelf();
        $selectMock->expects($this->any())->method('joinLeft')->willReturnSelf();
        $selectMock->expects($this->any())->method('where')->willReturnSelf();

        $connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\AdapterInterface::class);
        $connectionMock->expects($this->any())->method('select')->willReturn($selectMock);
        $connectionMock->expects(
            $this->any()
        )->method(
            'fetchOne'
        )->willReturn($return);

        $this->resourceConnection->expects(
            $this->once()
        )->method(
            'getConnection'
        )->willReturn($connectionMock);

        $this->resourceConnection->expects(
            $this->any()
        )->method(
            'getTableName'
        )->willReturn('customer_entity');

        $configShare = $this->createMock(\Magento\Customer\Model\Config\Share::class);
        $configShare->expects($this->once())->method('isWebsiteScope')->willReturn(true);

        $this->customer->expects($this->once())->method('getSharingConfig')->willReturn($configShare);
    }

    public function testGetCustomer()
    {
        $customerId = 1;
        $email = 'test@test.com';

        $this->customerFactory->expects($this->exactly(2))
                              ->method('create')
                              ->willReturn($this->customer);

        $this->customer->expects($this->once())
                       ->method('load')
                       ->with($customerId)
                       ->willReturnSelf();

        $this->customer->expects($this->once())
                       ->method('getId')
                       ->willReturn($customerId);

        $this->preapreResourceConnection(1);

        $this->assertEquals($customerId, $this->helper->prepareCustomerIdByEmail($email, [1], false));
        $this->assertEquals(null, $this->helper->prepareCustomerIdByEmail($email, [1], true));
    }

    public function testGetCustomerException()
    {
        $email = 'test@test.com';

        $this->customerFactory->expects($this->once())
                              ->method('create')
                              ->willReturn($this->customer);

        $this->expectException(LocalizedException::class);
        $this->preapreResourceConnection();
        $this->helper->prepareCustomerIdByEmail($email, [1,2], false);
    }
}
