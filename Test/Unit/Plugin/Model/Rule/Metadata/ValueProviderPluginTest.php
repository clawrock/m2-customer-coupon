<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Plugin\Model\Rule\Metadata;

use ClawRock\CustomerCoupon\Helper\Config;
use ClawRock\CustomerCoupon\Plugin\Model\Rule\Metadata\ValueProviderPlugin;
use Magento\SalesRule\Model\Rule\Metadata\ValueProvider;
use PHPUnit\Framework\TestCase;

class ValueProviderPluginTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Plugin\Model\Rule\Metadata\ValueProviderPlugin
     */
    protected $plugin;

    /**
     * @var \Closure
     */
    protected $proceed;

    /**
     * @var \Magento\SalesRule\Model\Rule\Metadata\ValueProvider
     */
    protected $subject;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->config = $this->getMockBuilder(Config::class)
                             ->setMethods(['isEnabled'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->plugin = $objectManager->getObject(
            ValueProviderPlugin::class,
            [
                'config' => $this->config
            ]
        );

        $this->proceed = function () {
            return $this->subject;
        };

        $this->subject = $this->getMockBuilder(ValueProvider::class)
                              ->setMethods()->disableOriginalConstructor()
                              ->getMock();
    }

    public function testAfterGetMetadataValuesConfigEnabled()
    {
        $result = [];

        $this->config->expects($this->once())->method('isEnabled')->willReturn(true);

        $result = $this->plugin->afterGetMetadataValues(
            $this->subject,
            $result
        );
        $this->assertTrue(empty($result));
    }

    public function testAfterGetMetadataValuesConfigDisabled()
    {
        $result = [];

        $this->config->expects($this->once())->method('isEnabled')->willReturn(false);

        $result = $this->plugin->afterGetMetadataValues(
            $this->subject,
            $result
        );
        $this->assertTrue(!empty($result));
        $this->assertEquals(false, $result['apply_to_shipping_methods']['arguments']['data']['config']['visible']);
    }
}
