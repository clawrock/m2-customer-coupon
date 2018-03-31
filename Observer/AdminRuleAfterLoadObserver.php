<?php

namespace ClawRock\CustomerCoupon\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AdminRuleAfterLoadObserver implements ObserverInterface
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Coupon
     */
    protected $couponHelper;

    public function __construct(
        \ClawRock\CustomerCoupon\Helper\Coupon $couponHelper
    ) {
        $this->couponHelper = $couponHelper;
    }

     /**
      * @param \Magento\Framework\Event\Observer $observer
      * @return void
      */
    public function execute(Observer $observer)
    {
        $rule = $observer->getRule();

        try {
            $coupon = $this->couponHelper->loadCouponByCode($rule->getCouponCode());
            $customerEmail = $this->couponHelper->getCustomerEmail($coupon->getCouponCustomerId());
        } catch (\Exception $e) {
            $customerEmail = '';
        }

        $rule->setCouponCustomerId($customerEmail);
    }
}
