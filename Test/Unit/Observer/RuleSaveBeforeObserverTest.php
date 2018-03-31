<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Observer;

use ClawRock\CustomerCoupon\Observer\RuleSaveBeforeObserver;
use Magento\Framework\Event\Observer;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\TestCase;

class RuleSaveBeforeObserverTest extends TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $rule;

    /**
     * @var \ClawRock\CustomerCoupon\Observer\RuleAfterLoadObserver
     */
    protected $observer;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->rule = $this->getMockBuilder(Rule::class)
                           ->setMethods()
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->observer = new RuleSaveBeforeObserver();
    }

    public function testSetCouponCustomer()
    {
        $eventObserver = new Observer([
            'rule' => $this->rule,
        ]);

        $this->observer->execute($eventObserver);
        $this->assertNull($this->rule->getCouponCustomerId());
    }

    public function testApplyShippingMethods()
    {
        $shippingMethods = [
            'freeshipping_freeshipping',
            'flatrate_flatrate'
        ];

        $expected = implode(',', $shippingMethods);

        $this->rule->setApplyToShippingMethods($shippingMethods);

        $eventObserver = new Observer([
            'rule' => $this->rule,
        ]);

        $this->observer->execute($eventObserver);
        $this->assertEquals($expected, $this->rule->getApplyToShippingMethods());
    }
}
