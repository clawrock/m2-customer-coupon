<?php

namespace ClawRock\CustomerCoupon\Plugin\Block\SalesRule;

class CouponsGridPlugin
{
    /**
     * @param  \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid $subject
     * @param  \Closure $proceed
     * @param  \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $collection
     * @return \Closure
     */
    public function aroundSetCollection(
        \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid $subject,
        \Closure $proceed,
        $collection
    ) {
        $collection->getSelect()->joinLeft(
            ['customer' => $collection->getTable('customer_entity')],
            'main_table.coupon_customer_id = customer.entity_id',
            ['email']
        );
        return $proceed($collection);
    }
}
