<?php

namespace ClawRock\CustomerCoupon\Model\Rule\Quote;

use Magento\SalesRule\Model\ResourceModel\Rule\Quote\Collection;

class CouponFilter extends Collection
{
    public function filterSpecialCouponCode($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $collection->getSelect()
                   ->joinLeft(
                       ['rule_coupons2' => $collection->getTable('salesrule_coupon')],
                       'main_table.rule_id = rule_coupons2.rule_id',
                       ['rule_coupons2.code']
                   );

        $collection->getSelect()->where('rule_coupons2.code LIKE ?', '%'.$value.'%');
        $collection->getSelect()->group('main_table.rule_id');

        return $this;
    }
}
