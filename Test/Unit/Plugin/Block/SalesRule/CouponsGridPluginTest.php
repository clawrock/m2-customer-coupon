<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Plugin\Block\SalesRule;

use ClawRock\CustomerCoupon\Plugin\Block\SalesRule\CouponsGridPlugin;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid;
use Magento\SalesRule\Model\ResourceModel\Coupon\Collection;
use PHPUnit\Framework\TestCase;

class CouponsGridPluginTest extends TestCase
{
    /**
     * @var \ClawRock\CustomerCoupon\Plugin\Block\SalesRule\CouponsGridPlugin
     */
    protected $plugin;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\Collection
     */
    protected $collection;

    /**
     * @var \Closure
     */
    protected $proceed;

    /**
     * @var \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid
     */
    protected $subject;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->plugin = $objectManager->getObject(CouponsGridPlugin::class);

        $this->collection = $this->getMockBuilder(Collection::class)
                                 ->setMethods(['getSelect', 'getTable'])
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->proceed = function () {
            return $this->subject;
        };

        $this->subject = $this->getMockBuilder(Grid::class)
                              ->setMethods()
                              ->disableOriginalConstructor()
                              ->getMock();
    }

    public function testAroundSetCollection()
    {
        $this->collection->getSelect();

        $selectMock = $this->createMock(Select::class);
        $selectMock->expects($this->any())->method('joinLeft')->willReturnSelf();

        $this->collection->expects($this->once())->method('getTable')->willReturn('customer_entity');
        $this->collection->expects($this->once())->method('getSelect')->willReturn($selectMock);

        $result = $this->plugin->aroundSetCollection(
            $this->subject,
            $this->proceed,
            $this->collection
        );

        $this->assertInstanceOf(Grid::class, $result);
    }
}
