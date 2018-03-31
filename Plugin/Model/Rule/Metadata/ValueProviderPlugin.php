<?php

namespace ClawRock\CustomerCoupon\Plugin\Model\Rule\Metadata;

class ValueProviderPlugin
{
    const FREE_SHIPPING_METHODS_FIELDSET = 'apply_to_shipping_methods';

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Config
     */
    private $config;

    /**
     * @param \ClawRock\CustomerCoupon\Helper\Config $config
     */
    public function __construct(\ClawRock\CustomerCoupon\Helper\Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param  \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject
     * @param  array                                               $result
     * @return array
     */
    public function afterGetMetadataValues(\Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject, $result)
    {
        if (!$this->config->isEnabled()) {
            $result[self::FREE_SHIPPING_METHODS_FIELDSET] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'visible' => false,
                            'disabled' => true,
                            'componentType' => 'columns'
                        ]
                    ]
                ],
            ];
        }

        return $result;
    }
}
