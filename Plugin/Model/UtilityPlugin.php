<?php

namespace ClawRock\CustomerCoupon\Plugin\Model;

use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Utility;

class UtilityPlugin
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     */
    public function __construct(\Magento\SalesRule\Model\CouponFactory $couponFactory)
    {
        $this->couponFactory = $couponFactory;
    }

    /**
     * @param  \Magento\SalesRule\Model\Utility   $subject
     * @param  \Closure                           $proceed
     * @param  \Magento\SalesRule\Model\Rule      $rule
     * @param  \Magento\Quote\Model\Quote\Address $address
     * @return \Closure | bool
     */
    public function aroundCanProcessRule(
        Utility $subject,
        \Closure $proceed,
        Rule $rule,
        Address $address
    ) {
        if ($rule->getCouponType() != \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON) {
            $couponCode = $address->getQuote()->getCouponCode();
            if ($couponCode !== "") {
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if ($coupon->getId()) {
                    $customerCoupon = $coupon->getCouponCustomerId();
                    $customerId = $address->getQuote()->getCustomerId();
                    if ($customerCoupon && $customerCoupon != $customerId) {
                        return false;
                    }
                }
            }
        }
        return $proceed($rule, $address);
    }
}
