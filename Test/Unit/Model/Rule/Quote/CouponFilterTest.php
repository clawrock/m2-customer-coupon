<?php

namespace ClawRock\CustomerCoupon\Test\Unit\Model\Rule\Quote;

use ClawRock\CustomerCoupon\Model\Rule\Quote\CouponFilter;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter;
use Magento\Framework\DB\Select;
use Magento\SalesRule\Model\ResourceModel\Rule\Quote\Collection;
use PHPUnit\Framework\TestCase;

class CouponFilterTest extends TestCase
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\Quote\Collection
     */
    protected $collection;

    /**
     * @var \ClawRock\CustomerCoupon\Model\Rule\Quote\CouponFilter
     */
    protected $model;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->collection = $this->getMockBuilder(Collection::class)
                                 ->setMethods(['getSelect', 'getTable'])
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->model = $this->getMockBuilder(CouponFilter::class)
                            ->setMethods()
                            ->disableOriginalConstructor()
                            ->getMock();
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testFilterSpecialCouponCode($columnValue)
    {
        $filter = $this->getMockBuilder(AbstractFilter::class)
                       ->setMethods(['getValue'])
                       ->disableOriginalConstructor()
                       ->getMock();
        $filter->expects($this->once())->method('getValue')->willReturn($columnValue);

        $column = $this->getMockBuilder(Column::class)
                       ->setMethods(['getFilter'])
                       ->disableOriginalConstructor()
                       ->getMock();

        $column->expects($this->once())->method('getFilter')->willReturn($filter);

        if ($columnValue) {
            $selectMock = $this->createMock(Select::class);
            $selectMock->expects($this->any())->method('from')->willReturnSelf();
            $selectMock->expects($this->any())->method('joinLeft')->willReturnSelf();
            $selectMock->expects($this->any())->method('where')->willReturnSelf();
            $selectMock->expects($this->any())->method('group')->willReturnSelf();
            $this->collection->expects($this->once())->method('getTable')->willReturn('salesrule_coupon');
            $this->collection->expects($this->exactly(3))->method('getSelect')->willReturn($selectMock);
        }

        $this->assertInstanceOf(CouponFilter::class, $this->model->filterSpecialCouponCode($this->collection, $column));
    }

    public function valuesProvider()
    {
        return [
            ['test'],
            [null]
        ];
    }
}
