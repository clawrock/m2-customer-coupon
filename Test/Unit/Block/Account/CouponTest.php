<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Block\Account;

use ClawRock\CustomerCoupon\Block\Account\Coupon;
use ClawRock\CustomerCoupon\Helper\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\SalesRule\Model\ResourceModel\Coupon\Collection;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\Theme\Block\Html\Pager;
use PHPUnit\Framework\TestCase;

class CouponTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Helper\Config
     */
    protected $helper;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory
     */
    protected $couponCollectionFactory;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\Collection
     */
    protected $couponCollection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Theme\Block\Html\Pager
     */
    protected $pagerBlock;

    /**
     * @var \ClawRock\CustomerCoupon\Block\Account\Coupon
     */
    protected $block;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->couponCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
                                              ->setMethods(['create'])
                                              ->disableOriginalConstructor()
                                              ->getMock();
        $this->couponCollection = $this->getMockBuilder(Collection::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();

        $this->customerSession = $this->getMockBuilder(Session::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
                                         ->disableOriginalConstructor()
                                         ->getMock();
        $this->helper = $this->getMockBuilder(Config::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->layout = $this->getMockBuilder(LayoutInterface::class)
                             ->getMockForAbstractClass();

        $this->pagerBlock = $this->getMockBuilder(Pager::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $objectManager->getObject(
            Coupon::class,
            [
                'context'                 => $contextMock,
                'couponCollectionFactory' => $this->couponCollectionFactory,
                'customerSession'         => $this->customerSession,
                'resource'                => $this->resourceConnection,
                'config'                  => $this->helper,
                'data'                    => []
            ]
        );
    }

    public function testGetCustomMessage()
    {
        $this->helper->expects($this->once())
                     ->method('getCustomMessage')
                     ->willReturn('You have no coupons assigned.');

        $this->assertEquals('You have no coupons assigned.', $this->block->getCustomMessage());
    }

    public function prepareCollection()
    {
        $customerId = 1;

        $this->couponCollectionFactory->expects($this->once())
                                      ->method('create')
                                      ->willReturn($this->couponCollection);

        $this->couponCollection->expects($this->once())
                               ->method('addFieldToSelect')
                               ->with(['code', 'usage_limit', 'times_used'])
                               ->willReturnSelf();

        $this->couponCollection->expects($this->once())
                               ->method('join')
                               ->willReturnSelf();

        $this->couponCollection->expects($this->exactly(2))
                               ->method('addFieldToFilter')
                               ->willReturnMap(
                                   [
                                       ['coupon_customer_id', $customerId, $this->couponCollection],
                                       ['is_active', true, $this->couponCollection],
                                   ]
                               );

        $this->couponCollection->expects($this->once())
                               ->method('setOrder')
                               ->with('created_at', 'desc')
                               ->willReturnSelf();

        $this->resourceConnection->expects(
            $this->once()
        )->method(
            'getTableName'
        )->willReturn('salesrule');
    }

    public function testGetCustomerCoupons()
    {
        $this->customerSession->expects($this->once())
                              ->method('getCustomerId')
                              ->willReturn(1);
        $this->prepareCollection();

        $this->assertInstanceOf(Collection::class, $this->block->getCustomerCoupons());
    }

    public function testGetCustomerCouponsWillReturnFalse()
    {
        $this->customerSession->expects($this->once())
                              ->method('getCustomerId')
                              ->willReturn(null);

        $this->assertFalse($this->block->getCustomerCoupons());
    }

    public function testPrepareLayout()
    {
        $this->customerSession->expects($this->exactly(3))
                              ->method('getCustomerId')
                              ->willReturn(1);

        $this->prepareCollection();

        $this->layout->expects($this->once())
                     ->method('createBlock')
                     ->with(Pager::class)
                     ->willReturn($this->pagerBlock);

        $this->pagerBlock->expects($this->once())
                         ->method('setCollection')
                         ->with($this->couponCollection)
                         ->willReturnSelf();

        $this->layout->expects($this->once())->method('setChild')->with(null, null, 'pager');

        $this->block->setLayout($this->layout);

        $this->layout->expects($this->once())->method('getChildName')->willReturn('customer.coupons.pager');
        $this->layout->expects($this->once())->method('renderElement')->willReturn('<div class="pager"></div>');

        $this->assertEquals('<div class="pager"></div>', $this->block->getPagerHtml());
    }
}
