<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Plugin\Model;

use ClawRock\CustomerCoupon\Plugin\Model\UtilityPlugin;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Utility;
use PHPUnit\Framework\TestCase;

class UtilityPluginTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Plugin\Model\UtilityPlugin
     */
    protected $plugin;

    /**
     * @var \Closure
     */
    protected $proceed;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $coupon;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\Quote\Model\Quote\Address
     */
    protected $address;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\SalesRule\Model\Utility
     */
    protected $subject;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->couponFactory = $this->getMockBuilder(CouponFactory::class)
                                    ->setMethods(['create'])
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->coupon = $this->getMockBuilder(Coupon::class)
                             ->setMethods(['load', 'getId', 'getCouponCustomerId'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->rule = $this->getMockBuilder(Rule::class)
                           ->setMethods()
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->address = $this->getMockBuilder(Address::class)
                              ->setMethods(['getQuote'])
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->quote = $this->getMockBuilder(Quote::class)
                            ->setMethods(['getCustomerId', 'getCouponCode'])
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->proceed = function () {
            return $this->subject;
        };

        $this->subject = $this->getMockBuilder(Utility::class)
                              ->setMethods()
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->plugin = $objectManager->getObject(
            UtilityPlugin::class,
            [
                'couponFactory' => $this->couponFactory
            ]
        );
    }

    public function prepareMock($couponCode, $quoteCustomer, $couponCustomer)
    {
        $this->rule->setCouponType(Rule::COUPON_TYPE_SPECIFIC);
        $this->address->expects($this->exactly(2))->method('getQuote')->willReturn($this->quote);

        $this->quote->expects($this->once())->method('getCouponCode')->willReturn($couponCode);
        $this->quote->expects($this->once())->method('getCustomerId')->willReturn($quoteCustomer);

        $this->couponFactory->expects($this->once())->method('create')->willReturn($this->coupon);
        $this->coupon->expects($this->once())->method('load')->with($couponCode, 'code')->willReturnSelf();
        $this->coupon->expects($this->once())->method('getCouponCustomerId')->willReturn($couponCustomer);
        $this->coupon->expects($this->once())->method('getId')->willReturn(1);
    }

    public function testAroundCanProcessRule()
    {
        $this->prepareMock('TEST', 1, 1);

        $result = $this->plugin->aroundCanProcessRule(
            $this->subject,
            $this->proceed,
            $this->rule,
            $this->address
        );

        $this->assertInstanceOf(Utility::class, $result);
    }

    public function testAroundCanProcessRuleWillReturnFalse()
    {
        $this->prepareMock('TEST', 1, 2);

        $result = $this->plugin->aroundCanProcessRule(
            $this->subject,
            $this->proceed,
            $this->rule,
            $this->address
        );

        $this->assertFalse($result);
    }
}
