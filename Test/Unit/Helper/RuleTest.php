<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Helper;

use ClawRock\CustomerCoupon\Helper\Rule as ConfigRule;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Helper\Rule
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryMock;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $ruleMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->registryMock = $this->getMockBuilder(Registry::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(['register', 'registry'])
                                   ->getMock();

        $this->ruleMock = $this->getMockBuilder(Rule::class)
                               ->disableOriginalConstructor()
                               ->setMethods(['getApplyToShippingMethods'])
                               ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $contextMock->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->helper = $objectManager->getObject(
            ConfigRule::class,
            [
                'context'  => $contextMock,
                'registry' => $this->registryMock
            ]
        );
    }

    public function prepareData($applyMethods, $expected)
    {
        $this->ruleMock->expects($this->once())
                       ->method('getApplyToShippingMethods')
                       ->willReturn($applyMethods);

        $this->assertInstanceOf(ConfigRule::class, $this->helper->registerApplicableShippingMethods($this->ruleMock));
        $this->assertEquals(
            $expected,
            $this->registryMock->registry(ConfigRule::SALESRULE_APPLY_TO_SHIPPING_METHODS_REGISTRY_KEY)
        );
    }

    /**
     * @dataProvider methodsProvider
     */
    public function testRegisterApplicableShippingMethods(
        $applyMethods,
        $currentMethods,
        $expected,
        $registry
    ) {
        $this->registryMock->expects($this->exactly(3))
                           ->method('registry')
                           ->with(ConfigRule::SALESRULE_APPLY_TO_SHIPPING_METHODS_REGISTRY_KEY)
                           ->will($this->onConsecutiveCalls($currentMethods, $registry, $expected));

        $this->prepareData($applyMethods, $expected);
    }

    public function methodsProvider()
    {
        $applyMethods = ['freeshipping_freeshipping','flatrate_flatrate'];
        $currentMethods = ['ups_1DM', 'ups_WXS'];
        $expected = array_merge($applyMethods, $currentMethods);

        return [
            [$applyMethods, $currentMethods, $expected, []],
            [$applyMethods, null, $applyMethods, ['freeshipping']]
        ];
    }
}
