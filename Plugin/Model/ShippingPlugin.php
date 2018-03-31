<?php

namespace ClawRock\CustomerCoupon\Plugin\Model;

use ClawRock\CustomerCoupon\Helper\Rule;
use Magento\Shipping\Model\Shipping;

class ShippingPlugin
{
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
     * @param  \Magento\Shipping\Model\Shipping $subject
     * @return \Magento\Shipping\Model\Shipping
     */
    public function afterCollectRates(Shipping $subject)
    {
        $applyTo = $this->registry->registry(Rule::SALESRULE_APPLY_TO_SHIPPING_METHODS_REGISTRY_KEY);
        if (is_array($applyTo) && !empty($applyTo)) {
            foreach ($subject->getResult()->getAllRates() as $rate) {
                if (in_array($rate->getCarrier(). '_' .$rate->getMethod(), $applyTo)) {
                    $rate->setPrice(0);
                    $rate->setCost(0);
                }
            }
        }
        return $subject;
    }
}
