<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Plugin\Model;

use ClawRock\CustomerCoupon\Helper\Rule;
use ClawRock\CustomerCoupon\Plugin\Model\ShippingPlugin;
use ClawRock\CustomerCoupon\Plugin\Model\UtilityPlugin;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Shipping;
use PHPUnit\Framework\TestCase;

class ShippingPluginTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Plugin\Model\ShippingPlugin
     */
    protected $plugin;

    /**
     * @var \Closure
     */
    protected $proceed;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Shipping\Model\Rate\Result
     */
    protected $result;

    /**
     * @var \Magento\Shipping\Model\Shipping
     */
    protected $subject;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->registry = $this->getMockBuilder(Registry::class)
                               ->setMethods(['registry'])
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->result = $this->getMockBuilder(Result::class)
                             ->setMethods(['getAllRates'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->proceed = function () {
            return $this->subject;
        };

        $this->subject = $this->getMockBuilder(Shipping::class)
                              ->setMethods(['getResult'])
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->plugin = $objectManager->getObject(
            ShippingPlugin::class,
            [
                'registry' => $this->registry
            ]
        );
    }

    public function getMethods()
    {
        $result = [];

        $method = $this->getMockBuilder(Method::class)
                       ->disableOriginalConstructor()
                       ->setMethods(['getCarrier', 'getMethod', 'setPrice', 'setCost'])
                       ->getMock();

        $method->expects($this->exactly(2))
               ->method('setPrice')
               ->with(0)
               ->willReturnSelf();

        $method->expects($this->exactly(2))
               ->method('setCost')
               ->with(0)
               ->willReturnSelf();

        $method->expects($this->exactly(3))
               ->method('getCarrier')
               ->will($this->onConsecutiveCalls('flatrate', 'freeshipping', 'usps'));

        $method->expects($this->exactly(3))
               ->method('getMethod')
               ->will($this->onConsecutiveCalls('flatrate', 'freeshipping', '1'));

        for ($i = 0; $i <= 2; $i++) {
            $result[] = $method;
        }
        return $result;
    }

    public function testAfterCollectRates()
    {
        $applyTo = ['flatrate_flatrate','freeshipping_freeshipping', 'dhl_1'];

        $this->registry->expects($this->once())
                       ->method('registry')
                       ->with(Rule::SALESRULE_APPLY_TO_SHIPPING_METHODS_REGISTRY_KEY)
                       ->willReturn($applyTo);

        $this->subject->expects($this->once())
                      ->method('getResult')
                      ->willReturn($this->result);

        $this->result->expects($this->once())
                     ->method('getAllRates')
                     ->willReturn($this->getMethods());

        $result = $this->plugin->afterCollectRates(
            $this->subject
        );

        $this->assertInstanceOf(Shipping::class, $result);
    }
}
