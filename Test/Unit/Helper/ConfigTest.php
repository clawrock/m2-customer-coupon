<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Helper;

use ClawRock\CustomerCoupon\Helper\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Helper\Config
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
                                      ->getMockForAbstractClass();
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->helper = $objectManager->getObject(
            Config::class,
            [
                'context' => $contextMock,
            ]
        );
    }

    public function testIsEnabled()
    {
        $this->scopeConfigMock->method('getValue')
                              ->with(Config::CONFIG_ENABLED)
                              ->willReturn(1);
        $this->assertEquals(1, $this->helper->isEnabled());
    }

    public function testGetCustomMessage()
    {
        $this->scopeConfigMock->method('getValue')
                              ->with(Config::CONFIG_CUSTOM_MESSAGE)
                              ->willReturn('You have no coupons assigned.');
        $this->assertEquals('You have no coupons assigned.', $this->helper->getCustomMessage());
    }
}
