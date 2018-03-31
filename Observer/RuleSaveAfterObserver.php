<?php

namespace ClawRock\CustomerCoupon\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RuleSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \ClawRock\CustomerCoupon\Helper\Coupon
     */
    protected $couponHelper;

    /**
     * @param \ClawRock\CustomerCoupon\Helper\Coupon $couponHelper
     */
    public function __construct(
        \ClawRock\CustomerCoupon\Helper\Coupon $couponHelper
    ) {
        $this->couponHelper = $couponHelper;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $rule = $observer->getRule();

        $coupon = $rule->getPrimaryCoupon();
        if (!$coupon->getCode()) {
            return;
        }

        if ($rule->hasCouponCustomerId() && !empty($rule->getCouponCustomerId())) {
            $customer = $this->couponHelper->getCustomerByEmail(
                $rule->getCouponCustomerId(),
                $rule->getWebsiteIds()
            );
            $couponCustomerId = $customer->getId();
        } else {
            $couponCustomerId = null;
        }
        $rule->getPrimaryCoupon()->setCouponCustomerId($couponCustomerId)->save();
    }
}
