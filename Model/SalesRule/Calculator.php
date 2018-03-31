<?php

namespace ClawRock\CustomerCoupon\Model\SalesRule;

use Magento\OfflineShipping\Model\SalesRule\Rule;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Calculator extends \Magento\OfflineShipping\Model\SalesRule\Calculator
{
    /**
     * @var \ClawRock\CustomerCoupon\Helper\Rule
     */
    protected $ruleHelper;

    /**
     * @param \ClawRock\CustomerCoupon\Helper\Rule $rule
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param Utility $utility
     * @param RulesApplier $rulesApplier
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param Validator\Pool $validators
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \ClawRock\CustomerCoupon\Helper\Rule $ruleHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\SalesRule\Model\Utility $utility,
        \Magento\SalesRule\Model\RulesApplier $rulesApplier,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\SalesRule\Model\Validator\Pool $validators,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ruleHelper = $ruleHelper;
        parent::__construct(
            $context,
            $registry,
            $collectionFactory,
            $catalogData,
            $utility,
            $rulesApplier,
            $priceCurrency,
            $validators,
            $messageManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @param  \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    public function processFreeShipping(AbstractItem $item)
    {
        $address = $item->getAddress();
        $item->setFreeShipping(false);
        foreach ($this->_getRules($address) as $rule) {
            /* @var $rule \Magento\SalesRule\Model\Rule */
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                continue;
            }

            switch ($rule->getSimpleFreeShipping()) {
                case Rule::FREE_SHIPPING_ITEM:
                    $item->setFreeShipping($rule->getDiscountQty() ? $rule->getDiscountQty() : true);
                    break;

                case Rule::FREE_SHIPPING_ADDRESS:
                    $address->setFreeShipping(false);
                    $this->ruleHelper->registerApplicableShippingMethods($rule);
                    break;
            }
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        return $this;
    }
}
