<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Observer;

use ClawRock\CustomerCoupon\Helper\Coupon as CouponHelper;
use ClawRock\CustomerCoupon\Observer\RuleSaveAfterObserver;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\TestCase;

class RuleSaveAfterObserverTest extends TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $rule;

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Coupon
     */
    protected $couponHelper;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $coupon;

    /**
     * @var \ClawRock\CustomerCoupon\Observer\RuleAfterLoadObserver
     */
    protected $observer;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->couponHelper = $this->getMockBuilder(CouponHelper::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->coupon = $this->getMockBuilder(Coupon::class)
                             ->setMethods(['getCode', 'save'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->customer = $this->getMockBuilder(Customer::class)
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->rule = $this->getMockBuilder(Rule::class)
                           ->setMethods(['getPrimaryCoupon', 'getWebsiteIds'])
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->observer = new RuleSaveAfterObserver($this->couponHelper);
    }

    public function testAddCouponCustomerId()
    {
        $couponCode = 'TEST12';
        $customerEmail = 'test@example.com';
        $customerId = 1;

        $this->rule->setCouponCustomerId($customerEmail);
        $this->rule->expects($this->exactly(2))->method('getPrimaryCoupon')->willReturn($this->coupon);
        $this->rule->expects($this->once())->method('getWebsiteIds')->willReturn([1,2]);

        $this->coupon->expects($this->once())->method('save')->willReturnSelf();
        $this->coupon->expects($this->once())->method('getCode')->willReturn($couponCode);

        $this->couponHelper->expects($this->once())->method('getCustomerByEmail')->willReturn($this->customer);

        $this->customer->expects($this->once())->method('getId')->willReturn($customerId);

        $eventObserver = new Observer([
            'rule' => $this->rule,
        ]);

        $this->observer->execute($eventObserver);
        $this->assertEquals($customerId, $this->coupon->getCouponCustomerId());
    }

    public function testAddCouponCustomerIdNull()
    {
        $couponCode = 'TEST12';

        $this->rule->expects($this->exactly(2))->method('getPrimaryCoupon')->willReturn($this->coupon);
        $this->coupon->expects($this->once())->method('save')->willReturnSelf();
        $this->coupon->expects($this->once())->method('getCode')->willReturn($couponCode);

        $eventObserver = new Observer([
            'rule' => $this->rule,
        ]);

        $this->observer->execute($eventObserver);
        $this->assertNull($this->coupon->getCouponCustomerId());
    }

    public function testCouponWithoutCode()
    {
        $this->rule->expects($this->once())->method('getPrimaryCoupon')->willReturn($this->coupon);
        $this->coupon->expects($this->once())->method('getCode')->willReturn(null);

        $eventObserver = new Observer([
            'rule' => $this->rule,
        ]);

        $this->observer->execute($eventObserver);
        $this->assertNull($this->coupon->getCouponCustomerId());
    }
}
