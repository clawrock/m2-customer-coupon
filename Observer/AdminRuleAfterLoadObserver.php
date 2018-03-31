<?php

namespace ClawRock\CustomerCoupon\Observer;

use Magento\Framework\Event\ObserverInterface;

class AdminRuleAfterLoadObserver implements ObserverInterface
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    public function __construct(
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->couponFactory = $couponFactory;
        $this->customerFactory = $customerFactory;
    }

     /**
      * @param \Magento\Framework\Event\Observer $observer
      * @return void
      */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getRule();
        $coupon = $this->couponFactory->create()->loadByCode($rule->getCouponCode());
        $customer = $this->customerFactory->create()->load($coupon->getCouponCustomerId());

        $rule->setCouponCustomerId($customer->getEmail());
    }
}
