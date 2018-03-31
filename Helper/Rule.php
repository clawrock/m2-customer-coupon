<?php

namespace ClawRock\CustomerCoupon\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Rule extends AbstractHelper
{
    const SALESRULE_APPLY_TO_SHIPPING_METHODS_REGISTRY_KEY = 'clawrock_customercoupon_apply_to_shipping_methods';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return $this
     */
    public function registerApplicableShippingMethods(\Magento\SalesRule\Model\Rule $rule)
    {
        $currentMethods = $this->registry->registry(self::SALESRULE_APPLY_TO_SHIPPING_METHODS_REGISTRY_KEY);
        $methodsToAdd = $rule->getApplyToShippingMethods();
        if (is_array($currentMethods)) {
            $newMethods = array_unique(array_merge($currentMethods, $methodsToAdd));
        } else {
            $newMethods = $methodsToAdd;
        }

        if ($this->registry->registry(self::SALESRULE_APPLY_TO_SHIPPING_METHODS_REGISTRY_KEY)) {
            $this->registry->unregister(self::SALESRULE_APPLY_TO_SHIPPING_METHODS_REGISTRY_KEY);
        }

        $this->registry->register(self::SALESRULE_APPLY_TO_SHIPPING_METHODS_REGISTRY_KEY, $newMethods);
        return $this;
    }
}
