<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Observer;

use ClawRock\CustomerCoupon\Observer\AdminRuleAfterLoadObserver;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\TestCase;

class AdminRuleAfterLoadObserverTest extends TestCase
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

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
     * @var \ClawRock\CustomerCoupon\Observer\AdminRuleAfterLoadObserver
     */
    protected $observer;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->couponFactory = $this->getMockBuilder(CouponFactory::class)
                                    ->setMethods(['create'])
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->coupon = $this->getMockBuilder(Coupon::class)
                             ->setMethods(['loadByCode', 'getCouponCustomerId'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->customerFactory = $this->getMockBuilder(CustomerFactory::class)
                                      ->setMethods(['create'])
                                      ->disableOriginalConstructor()
                                      ->getMock();

        $this->customer = $this->getMockBuilder(Customer::class)
                                ->setMethods(['load', 'getEmail'])
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->rule = $this->createPartialMock(Rule::class, ['getCouponCode']);

        $this->observer = new AdminRuleAfterLoadObserver($this->couponFactory, $this->customerFactory);
    }

    public function testAddCouponCustomerId()
    {
        $couponCode = 'test-coupon';
        $customerId = 1;
        $customerEmail = 'test@test.com';

        $this->couponFactory->expects($this->once())->method('create')->willReturn($this->coupon);
        $this->rule->expects($this->once())->method('getCouponCode')->willReturn($couponCode);
        $this->coupon->expects($this->once())->method('loadByCode')->with($couponCode)->willReturnSelf();
        $this->coupon->expects($this->once())->method('getCouponCustomerId')->willReturn($customerId);
        $this->customerFactory->expects($this->once())->method('create')->willReturn($this->customer);
        $this->customer->expects($this->once())->method('load')->with($customerId)->willReturnSelf();
        $this->customer->expects($this->once())->method('getEmail')->willReturn($customerEmail);

        $eventObserver = new Observer([
            'rule' => $this->rule,
        ]);

        $this->observer->execute($eventObserver);
        $this->assertEquals($customerEmail, $this->rule->getCouponCustomerId());
    }
}
