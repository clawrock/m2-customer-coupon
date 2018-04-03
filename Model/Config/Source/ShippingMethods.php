<?php

namespace ClawRock\CustomerCoupon\Model\Config\Source;

class ShippingMethods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Shipping\Model\Config $shippingConfig
     */
    public function __construct(
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry
    ) {
        $this->shippingConfig = $shippingConfig;
        $this->scopeConfig = $scopeConfig;
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $rule = $this->registry->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE);
            $this->options = [];
            foreach ($this->shippingConfig->getAllCarriers() as $carrierCode => $carrierModel) {
                $carrierTitle = $this->scopeConfig->getValue('carriers/' . $carrierCode . '/title');
                foreach ($carrierModel->getAllowedMethods() as $code => $name) {
                    if ($name) {
                        $this->options[] = [
                            'value' => "{$carrierCode}_{$code}",
                            'label' => "{$carrierTitle} - {$name}"
                        ];
                    }
                }
            }

            $methodCodes = array_column($this->options, 'value');
            if ($rule->getApplyToShippingMethods() !== null) {
                foreach ($rule->getApplyToShippingMethods() as $shippingMethod) {
                    if (!in_array($shippingMethod, $methodCodes)) {
                        $this->options[] = [
                            'value' => $shippingMethod,
                            'label' => $shippingMethod.' - currently not allowable'
                        ];
                    }
                }
            }
        }

        return $this->options;
    }
}
