<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Plugin\Model;

use ClawRock\CustomerCoupon\Helper\Coupon as CouponHelper;
use ClawRock\CustomerCoupon\Plugin\Model\CouponRepositoryPlugin;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponExtension;
use Magento\SalesRule\Api\Data\CouponExtensionFactory;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\TestCase;

class CouponRepositoryPluginTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Plugin\Model\CouponRepositoryPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\SalesRule\Api\CouponExtensionFactory
     */
    protected $couponExtensionFactory;

    /**
     * @var \Magento\SalesRule\Api\CouponExtension
     */
    protected $couponExtension;

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Coupon
     */
    protected $couponHelper;

    /**
     * @var \Magento\SalesRule\Api\Data\CouponInterface
     */
    protected $entity;

    /**
     * @var \Magento\Framework\Api\Search\SearchResult
     */
    protected $searchResult;

    /**
     * @var \Closure
     */
    protected $proceed;

    /**
     * @var \Magento\SalesRule\Api\CouponRepositoryInterface
     */
    protected $subject;

    protected $couponCustomerId = 1;
    protected $customerEmail = 'test@test.com';

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->couponExtensionFactory = $this->getMockBuilder(\Magento\SalesRule\Api\Data\CouponExtensionFactory::class)
                                             ->setMethods(['create'])
                                             ->disableOriginalConstructor()
                                             ->getMock();

        $this->couponExtension = $this->getMockBuilder(\Magento\SalesRule\Api\Data\CouponExtensionInterface::class)
                                      ->setMethods(['getCouponCustomerId', 'setCouponCustomerId'])
                                      ->disableOriginalConstructor()
                                      ->getMock();

        $this->couponHelper = $this->getMockBuilder(CouponHelper::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->searchResult = $this->getMockBuilder(SearchResults::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->proceed = function () {
            return $this->subject;
        };

        $this->subject = $this->createMock(CouponRepositoryInterface::class);

        $this->entity = $this->getMockBuilder(Coupon::class)
                             ->setMethods(['setExtensionAttributes', 'getExtensionAttributes'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->plugin = $objectManager->getObject(
            CouponRepositoryPlugin::class,
            [
                'couponExtensionFactory' => $this->couponExtensionFactory,
                'couponHelper' => $this->couponHelper,
            ]
        );
    }

    public function prepareMock()
    {

        $this->couponExtensionFactory->expects($this->once())
                                   ->method("create")
                                   ->willReturn($this->couponExtension);

        $this->couponExtension->expects($this->once())
                              ->method('setCouponCustomerId')
                              ->with($this->customerEmail)
                              ->willReturnSelf();

        $this->entity->setCouponCustomerId($this->couponCustomerId);

        $this->couponHelper->expects($this->once())
                           ->method('getCustomerEmail')
                           ->willReturn($this->customerEmail);

        $this->entity->expects($this->once())
                     ->method('setExtensionAttributes')
                     ->with($this->couponExtension);
    }

    public function testAfterGetById()
    {
        $this->prepareMock();

        $result = $this->plugin->afterGetById(
            $this->subject,
            $this->entity
        );

        $this->assertInstanceOf(CouponInterface::class, $result);
    }

    public function testAfterGetByIdWillThrowException()
    {
        $this->couponExtensionFactory->expects($this->once())
                                   ->method("create")
                                   ->willReturn($this->couponExtension);

        $this->couponExtension->expects($this->once())
                              ->method('setCouponCustomerId')
                              ->with('')
                              ->willReturnSelf();

        $this->entity->setCouponCustomerId($this->couponCustomerId);

        $this->couponHelper->expects($this->once())
                           ->method('getCustomerEmail')
                           ->willThrowException(new LocalizedException(new Phrase("Customer doesn't exists.")));

        $this->entity->expects($this->once())
                     ->method('setExtensionAttributes')
                     ->with($this->couponExtension);

        $result = $this->plugin->afterGetById(
            $this->subject,
            $this->entity
        );
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

    public function prepareAroundSaveMock()
    {
        $ruleId = 1;

        $rule = $this->getMockBuilder(Rule::class)
                     ->setMethods(['getWebsiteIds'])
                     ->disableOriginalConstructor()
                     ->getMock();
        $rule->expects($this->once())
             ->method('getWebsiteIds')
             ->willReturn([]);

        $this->entity->expects($this->once())
                     ->method('getExtensionAttributes')
                     ->willReturn($this->couponExtension);

        $this->entity->setRuleId($ruleId);

        $this->couponExtension->expects($this->exactly(3))
                              ->method('getCouponCustomerId')
                              ->willReturn($this->customerEmail);

        $this->couponHelper->expects($this->once())
                           ->method('loadRule')
                           ->with($ruleId)
                           ->willReturn($rule);
    }

    public function testAroundSave()
    {
        $this->prepareAroundSaveMock();

        $this->couponHelper->expects($this->once())
                           ->method('prepareCustomerIdByEmail')
                           ->with($this->customerEmail, [], false)
                           ->willReturn($this->couponCustomerId);

        $result = $this->plugin->aroundSave(
            $this->subject,
            $this->proceed,
            $this->entity
        );

        $this->assertInstanceOf(\Magento\SalesRule\Api\CouponRepositoryInterface::class, $result);
    }

    public function testAfterSaveThrowException()
    {
        $this->prepareAroundSaveMock();

        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);

        $this->couponHelper->expects($this->once())
                           ->method('prepareCustomerIdByEmail')
                           ->with($this->customerEmail, [], false)
                           ->willThrowException(new \Exception('Exception message.'));

        $this->plugin->aroundSave(
            $this->subject,
            $this->proceed,
            $this->entity
        );
    }
}
