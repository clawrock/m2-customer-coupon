<?php

namespace ClawRock\CustomerCoupon\Plugin\Model;

use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Phrase;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;

class RuleRepositoryPlugin
{
    /**
     * @var \Magento\SalesRule\Api\Data\RuleExtensionFactory
     */
    protected $ruleExtensionFactory;

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Coupon
     */
    protected $couponHelper;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToModel
     */
    protected $toModelConverter;

    /**
     * @param \Magento\SalesRule\Api\Data\RuleExtensionFactory $ruleExtensionFactory
     * @param \ClawRock\SalesRule\Helper\Coupon                $couponHelper
     * @param \Magento\SalesRule\Model\Converter\ToModel       $toModelConverter
     */
    public function __construct(
        \Magento\SalesRule\Api\Data\RuleExtensionFactory $ruleExtensionFactory,
        \ClawRock\CustomerCoupon\Helper\Coupon $couponHelper,
        \Magento\SalesRule\Model\Converter\ToModel $toModelConverter
    ) {
        $this->ruleExtensionFactory = $ruleExtensionFactory;
        $this->couponHelper = $couponHelper;
        $this->toModelConverter = $toModelConverter;
    }

    /**
     * @param  \Magento\SalesRule\Api\RuleRepositoryInterface $subject
     * @param  \Magento\SalesRule\Api\Data\RuleInterface      $entity
     * @return \Magento\SalesRule\Model\Data\Rule
     */
    public function afterGetById(
        RuleRepositoryInterface $subject,
        RuleInterface $entity
    ) {
        $this->addShippingMethods($entity);

        return $entity;
    }

    public function afterGetList(
        RuleRepositoryInterface $subject,
        SearchResults $searchResult
    ) {
        foreach ($searchResult->getItems() as $rule) {
            $this->addShippingMethods($rule);
        }

        return $searchResult;
    }

    /**
     * @param  \Magento\SalesRule\Api\RuleRepositoryInterface $subject
     * @param  \Magento\SalesRule\Api\Data\RuleInterface      $entity
     * @return \Magento\SalesRule\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function afterSave(
        RuleRepositoryInterface $subject,
        RuleInterface $entity
    ) {
        $extensionAttributes = $entity->getExtensionAttributes();
        if (null !== $extensionAttributes &&
            null !== $extensionAttributes->getApplyToShippingMethods()
        ) {
            try {
                $shippingMethods = $extensionAttributes->getApplyToShippingMethods();
                $rule = $this->toModelConverter->toModel($entity);
                $rule->setApplyToShippingMethods($shippingMethods)->save();
            } catch (\Exception $e) {
                throw new CouldNotSaveException(new Phrase($e->getMessage()));
            }
        }
        return $entity;
    }

    /**
     * @param \Magento\SalesRule\Api\Data\RuleInterface $entity
     * @return this
     */
    protected function addShippingMethods(RuleInterface $entity)
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes == null) {
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->ruleExtensionFactory->create();
        }

        try {
            $rule = $this->toModelConverter->toModel($entity);
            if ($shippingMethods = $rule->getApplyToShippingMethods()) {
                $shippingMethods = array_filter($shippingMethods);
            }
        } catch (\Exception $e) {
            $shippingMethods = [];
        }

        $extensionAttributes->setApplyToShippingMethods($shippingMethods);
        $entity->setExtensionAttributes($extensionAttributes);

        return $this;
    }
}
