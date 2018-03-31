<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Model\Config\Source;

use ClawRock\CustomerCoupon\Model\Config\Source\ShippingMethods;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\Rule;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Config;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;

class ShippingMethodsTest extends TestCase
{
    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \ClawRock\CustomerCoupon\Model\Config\Source\ShippingMethods
     */
    protected $model;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->shippingConfig = $this->getMockBuilder(Config::class)
                                     ->disableOriginalConstructor()
                                     ->getMock();

        $this->registry = $this->getMockBuilder(Registry::class)
                               ->setMethods(['registry'])
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->model = $objectManager->getObject(
            ShippingMethods::class,
            [
                'shippingConfig' => $this->shippingConfig,
                'scopeConfig'    => $this->scopeConfig,
                'registry'       => $this->registry
            ]
        );
    }

    public function testToOptionArray()
    {
        $carrierModel = $this->getMockBuilder(AbstractCarrier::class)
                             ->setMethods(['getAllowedMethods'])
                             ->disableOriginalConstructor()
                             ->getMockForAbstractClass();

        $carriers = [
            'dhl' => $carrierModel,
            'flatrate'  => $carrierModel
        ];
        $allowedMethods = [

        ];

        $rule = $this->getMockBuilder(Rule::class)
                     ->setMethods(['getApplyToShippingMethods'])
                     ->disableOriginalConstructor()
                     ->getMock();

        $disabledCarrier = [
            'dhl_1',
            'dhl_3',
            'flatrate_flatrate'
        ];

        $rule->expects($this->once())
             ->method('getApplyToShippingMethods')
             ->willReturn($disabledCarrier);

        $this->registry->expects($this->once())
                       ->method('registry')
                       ->with(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE)
                       ->willReturn($rule);

        $this->shippingConfig->expects($this->once())
                             ->method('getAllCarriers')
                             ->willReturn($carriers);

        $this->scopeConfig->expects($this->any())
                          ->method('getValue')
                          ->willReturnMap(
                              [
                                  ['carriers/dhl/title', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, 'DHL'],
                                  ['carriers/flatrate/title',
                                      ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                                      null,
                                      'Flat Rate'
                                  ],
                              ]
                          );

        $carrierModel->expects($this->exactly(2))
                     ->method('getAllowedMethods')
                     ->will($this->onConsecutiveCalls(
                         [
                            'Q' => 'Easy shop',
                            'P' => 'Express worldwide'
                         ],
                         [
                            'flatrate' => 'Fixed'
                         ]
                     ));

        $result = $this->model->toOptionArray();
        $expectedResult = [
            [
                'value' => 'dhl_Q',
                'label' => 'DHL - Easy shop'
            ],
            [
                'value' => 'dhl_P',
                'label' => 'DHL - Express worldwide'
            ],
            [
                'value' => 'flatrate_flatrate',
                'label' => 'Flat Rate - Fixed'
            ],
            [
                'value' => 'dhl_1',
                'label' => 'dhl_1 - currently not allowable'
            ],
            [
                'value' => 'dhl_3',
                'label' => 'dhl_3 - currently not allowable'
            ],
        ];
        $this->assertInternalType('array', $result);
        $this->assertEquals($expectedResult, $result);
    }
}
