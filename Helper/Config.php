<?php

namespace ClawRock\CustomerCoupon\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const CONFIG_ENABLED = 'clawrock_customercoupon/general/enabled';
    const CONFIG_CUSTOM_MESSAGE = 'clawrock_customercoupon/general/custom_message';

    /**
     * @param null|string $scopeCode
     * @return boolean
     */
    public function isEnabled($store = null)
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_ENABLED, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param  null|string $store
     * @return string
     */
    public function getCustomMessage($store = null)
    {
        return $this->scopeConfig->getValue(self::CONFIG_CUSTOM_MESSAGE, ScopeInterface::SCOPE_STORE, $store);
    }
}
