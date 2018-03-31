<?php

namespace ClawRock\CustomerCoupon\Observer;

use Magento\Framework\Event\ObserverInterface;

class RuleSaveBeforeObserver implements ObserverInterface
{
    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getRule();
        if (empty($rule->getCouponCustomerId())) {
            $rule->setCouponCustomerId(null);
        }

        if ($rule->hasApplyToShippingMethods()) {
            $shippingMethods = $rule->getApplyToShippingMethods();
            if (!is_string($shippingMethods)) {
                $rule->setApplyToShippingMethods(implode(',', $shippingMethods));
            }
        }
    }
}
