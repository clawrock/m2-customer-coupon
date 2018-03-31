<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Plugin\Model;

use ClawRock\CustomerCoupon\Helper\Coupon;
use ClawRock\CustomerCoupon\Plugin\Model\RuleRepositoryPlugin;
use Magento\Framework\Api\SearchResults;
use Magento\SalesRule\Api\Data\RuleExtension;
use Magento\SalesRule\Api\Data\RuleExtensionFactory;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Converter\ToModel;
use Magento\SalesRule\Model\Data\Rule as DataRule;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\TestCase;

class RuleRepositoryPluginTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Plugin\Model\RuleRepositoryPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\SalesRule\Api\Data\RuleExtensionFactory
     */
    protected $ruleExtensionFactory;

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Coupon
     */
    protected $couponHelper;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToModel
     */
    protected $toModelConverter;

    /**
     * @var Magento\SalesRule\Model\Data\Rule
     */
    protected $entity;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\Framework\Api\Search\SearchResult
     */
    protected $searchResult;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    protected $subject;

    /**
     * @var array
     */
    protected $shippingMethods = ['freeshipping_freeshipping', 'flatrate_bestway'];

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->ruleExtensionFactory = $this->getMockBuilder(RuleExtensionFactory::class)
                                           ->setMethods(['create'])
                                           ->disableOriginalConstructor()
                                           ->getMock();

        $this->ruleExtension = $this->getMockBuilder(\Magento\SalesRule\Api\Data\RuleExtensionInterface::class)
                                    ->setMethods(['setApplyToShippingMethods', 'getApplyToShippingMethods'])
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->rule = $this->getMockBuilder(Rule::class)
                           ->setMethods(['save', 'setExtensionAttributes'])
                           ->disableOriginalConstructor()
                           ->getMock();

        $this->couponHelper = $this->getMockBuilder(Coupon::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->toModelConverter = $this->getMockBuilder(ToModel::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->searchResult = $this->getMockBuilder(SearchResults::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->subject = $this->createMock(RuleRepositoryInterface::class);

        $this->entity = $this->getMockBuilder(DataRule::class)
                             ->setMethods(['getExtensionAttributes', 'setExtensionAttributes'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->plugin = $objectManager->getObject(
            RuleRepositoryPlugin::class,
            [
                'ruleExtensionFactory' => $this->ruleExtensionFactory,
                'couponHelper' => $this->couponHelper,
                'toModelConverter' => $this->toModelConverter
            ]
        );
    }

    public function prepareMock()
    {

        $this->ruleExtensionFactory->expects($this->once())
                                   ->method("create")
                                   ->willReturn($this->ruleExtension);

        $this->toModelConverter->expects($this->once())
                               ->method('toModel')
                               ->with($this->entity)
                               ->willReturn($this->rule);

        $this->rule->setApplyToShippingMethods($this->shippingMethods);

        $this->ruleExtension->expects($this->once())
                            ->method('setApplyToShippingMethods')
                            ->with($this->shippingMethods)
                            ->willReturnSelf();

        $this->entity->expects($this->once())
                     ->method('setExtensionAttributes')
                     ->with($this->ruleExtension);
    }

    public function testAfterGetById()
    {
        $this->prepareMock();

        $result = $this->plugin->afterGetById(
            $this->subject,
            $this->entity
        );

        $this->assertInstanceOf(RuleInterface::class, $result);
    }

    public function testAfterGetList()
    {
        $this->prepareMock();

        $this->searchResult->expects($this->once())
                           ->method("getItems")
                           ->willReturn([$this->entity]);

        $result = $this->plugin->afterGetList(
            $this->subject,
            $this->searchResult
        );

        $this->assertInstanceOf(SearchResults::class, $result);
    }

    public function prepareAfterSaveMock()
    {
        $this->ruleExtension->expects($this->exactly(2))
                            ->method('getApplyToShippingMethods')
                            ->willReturn($this->shippingMethods);

        $this->toModelConverter->expects($this->once())
                               ->method('toModel')
                               ->with($this->entity)
                               ->willReturn($this->rule);

        $this->entity->expects($this->once())
                     ->method('getExtensionAttributes')
                     ->willReturn($this->ruleExtension);
    }

    public function testAfterSave()
    {
        $this->prepareAfterSaveMock();

        $this->rule->expects($this->once())
                   ->method('save')
                   ->willReturnSelf();

        $result = $this->plugin->afterSave(
            $this->subject,
            $this->entity
        );
        $this->assertInstanceOf(RuleInterface::class, $result);
    }

    public function testAfterSaveThrowException()
    {
        $this->prepareAfterSaveMock();

        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);

        $this->rule->expects($this->once())
                   ->method('save')
                   ->willThrowException(new \Exception("Couldn't save."));

        $this->plugin->afterSave(
            $this->subject,
            $this->entity
        );
    }
}
