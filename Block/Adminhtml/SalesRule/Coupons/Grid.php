<?php

namespace ClawRock\CustomerCoupon\Block\Adminhtml\SalesRule\Coupons;

use \ClawRock\CustomerCoupon\Block\Adminhtml\SalesRule\Coupons\Grid\Column\Renderer\Customer;

class Grid extends \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid
{
    /**
     * Define grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'clawrock-customer-coupon',
            [
                'header' => __('Customer'),
                'index' => 'email',
                'align' => 'center',
                'renderer' => Customer::class,
                'width' => '360'
            ]
        );
    }
}
