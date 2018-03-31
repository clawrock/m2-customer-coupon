<?php

namespace ClawRock\CustomerCoupon\Observer;

use Magento\Framework\Event\ObserverInterface;

class RuleAfterLoadObserver implements ObserverInterface
{
    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getRule();
        if (is_string($rule->getApplyToShippingMethods())) {
            $rule->setApplyToShippingMethods(explode(',', $rule->getApplyToShippingMethods()));
        }
    }
}
