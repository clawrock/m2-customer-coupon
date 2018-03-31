<?php

namespace ClawRock\CustomerCoupon\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RuleSaveBeforeObserver implements ObserverInterface
{
    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $rule = $observer->getRule();
        if (empty($rule->getCouponCustomerId())) {
            $rule->setCouponCustomerId(null);
        }

        if (!$rule->hasApplyToShippingMethods()) {
            return;
        }

        $shippingMethods = $rule->getApplyToShippingMethods();
        if (!is_string($shippingMethods)) {
            $rule->setApplyToShippingMethods(implode(',', $shippingMethods));
        }
    }
}
