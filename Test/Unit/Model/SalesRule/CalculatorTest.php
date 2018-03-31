<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Model\SalesRule;

use ClawRock\CustomerCoupon\Helper\Rule as RuleHelper;
use ClawRock\CustomerCoupon\Model\SalesRule\Calculator as ClawCalculator;
use ClawRock\CustomerCoupon\Observer\RuleAfterLoadObserver;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\Manager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\OfflineShipping\Model\SalesRule\Calculator;
use Magento\OfflineShipping\Model\SalesRule\Rule as SalesRule;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Utility;
use Magento\SalesRule\Model\Validator\Pool;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $rule;

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Rule
     */
    protected $ruleHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\SalesRule\Model\Utility
     */
    protected $utility;

    /**
     * @var \Magento\SalesRule\Model\RulesApplier
     */
    protected $rulesApplier;

    /**
     * @var \Magento\Quote\Model\Quote\Address
     */
    protected $addressMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $item;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogData;

    /**
     * @var \Magento\SalesRule\Model\Validator\Pool
     */
    protected $validators;

    /**
     * @var \Magento\Framework\Message\Manager
     */
    protected $messageManager;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected $ruleCollection;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \ClawRock\CustomerCoupon\Model\SalesRule\Calculator
     */
    protected $model;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->ruleHelper = $this->getMockBuilder(RuleHelper::class)
                             ->setMethods(['registerApplicableShippingMethods'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->registry = $this->getMockBuilder(Registry::class)
                               ->setMethods()
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->rule = $this->getMockBuilder(Rule::class)
                           ->setMethods(['getActions'])
                           ->disableOriginalConstructor()
                           ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->rulesApplier = $this->createPartialMock(
            RulesApplier::class,
            []
        );

        $this->addressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $this->item = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getAddress']
        );

        $this->catalogData = $this->createMock(Data::class);
        $this->utility = $this->createMock(Utility::class);
        $this->validators = $this->createPartialMock(Pool::class, ['getValidators']);
        $this->messageManager = $this->createMock(Manager::class);
        $this->ruleCollection = $this->getMockBuilder(RuleCollection::class)
                                     ->disableOriginalConstructor()
                                     ->getMock();

        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
                                        ->disableOriginalConstructor()
                                        ->setMethods(['create'])
                                        ->getMock();

        $this->prepareRuleCollectionMock($this->ruleCollection);

        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);

        $this->resource = $this->createPartialMock(
            AbstractResource::class,
            [
                'updateAttributes',
                'getConnection',
                '_construct',
                'getIdFieldName',
            ]
        );

        $this->resourceCollection = $this->createPartialMock(
            AbstractDb::class,
            [
                'getResource',
            ]
        );

        $this->model = $objectManager->getObject(
            ClawCalculator::class,
            [
                'ruleHelper'         => $this->ruleHelper,
                'context'            => $contextMock,
                'registry'           => $this->registry,
                'collectionFactory'  => $this->collectionFactory,
                'catalogData'        => $this->catalogData,
                'utility'            => $this->utility,
                'rulesApplier'       => $this->rulesApplier,
                'priceCurrency'      => $this->priceCurrency,
                'validators'         => $this->validators,
                'messageManager'     => $this->messageManager,
                'resource'           => $this->resource,
                'resourceCollection' => $this->resourceCollection,
                []
            ]
        );
    }

    /**
     * @param $ruleCollection
     */
    protected function prepareRuleCollectionMock($ruleCollection)
    {
        $this->ruleCollection->expects($this->any())
                             ->method('addFieldToFilter')
                             ->with('is_active', 1)
                             ->will($this->returnSelf());
        $this->ruleCollection->expects($this->any())
                             ->method('load')
                             ->willReturn([$this->rule]);
        $this->ruleCollection->expects($this->any())
                             ->method('setValidationFilter')
                             ->will($this->returnSelf());

        $this->collectionFactory->expects($this->any())
                                ->method('create')
                                ->will($this->returnValue($ruleCollection));
    }

    /**
     * @dataProvider rulesProvider
     */
    public function testProcessFreeShipping($canProcess, $validate, $simpleShipping, $stopProcess)
    {
        if ($canProcess) {
            $combine = $this->getMockBuilder(ProductCollection::class)
                            ->disableOriginalConstructor()
                            ->setMethods(['validate'])
                            ->getMock();

            $combine->expects($this->once())->method('validate')->willReturn($validate);
            $this->rule->expects($this->once())->method('getActions')->willReturn($combine);
        }

        $this->rule->setSimpleFreeShipping($simpleShipping);
        $this->rule->setStopRulesProcessing($stopProcess);

        $this->utility->expects($this->once())
                      ->method('canProcessRule')
                      ->with($this->rule, $this->addressMock)
                      ->willReturn($canProcess);

        $this->item->expects($this->once())->method('getAddress')->will($this->returnValue($this->addressMock));

        if ($simpleShipping == SalesRule::FREE_SHIPPING_ADDRESS && $canProcess && $validate) {
            $this->ruleHelper->expects($this->once())
                             ->method('registerApplicableShippingMethods')
                             ->with($this->rule)
                             ->willReturnSelf();
        }

        $this->assertInstanceOf(
            Calculator::class,
            $this->model->processFreeShipping($this->item)
        );
    }

    public function rulesProvider()
    {
        return [
            [true, true, SalesRule::FREE_SHIPPING_ITEM, true],
            [false, true, SalesRule::FREE_SHIPPING_ITEM, true],
            [false, true, SalesRule::FREE_SHIPPING_ADDRESS, true],
            [true, false, SalesRule::FREE_SHIPPING_ADDRESS, false],
            [true, true, SalesRule::FREE_SHIPPING_ADDRESS, true]
        ];
    }
}
