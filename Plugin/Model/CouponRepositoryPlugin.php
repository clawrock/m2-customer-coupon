<?php

namespace ClawRock\CustomerCoupon\Plugin\Model;

use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Phrase;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponInterface;

class CouponRepositoryPlugin
{
    /**
     * @var \Magento\SalesRule\Api\Data\CouponExtensionFactory
     */
    protected $couponExtensionFactory;

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Coupon
     */
    protected $couponHelper;

    /**
     * @param \Magento\SalesRule\Api\Data\CouponExtensionFactory $couponExtensionFactory
     * @param \ClawRock\SalesRule\Helper\Coupon                $couponHelper
     */
    public function __construct(
        \Magento\SalesRule\Api\Data\CouponExtensionFactory $couponExtensionFactory,
        \ClawRock\CustomerCoupon\Helper\Coupon $couponHelper
    ) {
        $this->couponExtensionFactory = $couponExtensionFactory;
        $this->couponHelper = $couponHelper;
    }

    /**
     * @param  \Magento\SalesRule\Api\CouponRepositoryInterface $subject
     * @param  \Magento\SalesRule\Api\Data\CouponInterface      $entity
     * @return \Magento\SalesRule\Model\Coupon
     */
    public function afterGetById(
        CouponRepositoryInterface $subject,
        CouponInterface $entity
    ) {
        $this->addCustomerToCoupon($entity);

        return $entity;
    }

    /**
     * @param  \Magento\SalesRule\Api\CouponRepositoryInterface $subject
     * @param  \Magento\Framework\Api\SearchResults             $searchResult
     * @return \Magento\Framework\Api\SearchResults
     */
    public function afterGetList(
        CouponRepositoryInterface $subject,
        SearchResults $searchResult
    ) {
        foreach ($searchResult->getItems() as $coupon) {
            $this->addCustomerToCoupon($coupon);
        }

        return $searchResult;
    }

    /**
     * @param  \Magento\SalesRule\Api\CouponRepositoryInterface $subject
     * @param  \Closure                                         $proceed
     * @param  \Magento\SalesRule\Api\Data\CouponInterface      $entity
     * @return \Closure
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function aroundSave(
        CouponRepositoryInterface $subject,
        \Closure $proceed,
        CouponInterface $entity
    ) {
        $extensionAttributes = $entity->getExtensionAttributes();
        if (null !== $extensionAttributes &&
            null !== $extensionAttributes->getCouponCustomerId()
        ) {
            try {
                $customerEmail = $extensionAttributes->getCouponCustomerId();
                $rule = $this->couponHelper->loadRule($entity->getRuleId());
                $remove = $customerEmail === "" ? true : false;
                $customerId = $this->couponHelper->prepareCustomerIdByEmail(
                    $extensionAttributes->getCouponCustomerId(),
                    $rule->getWebsiteIds(),
                    $remove
                );
                $entity->setCouponCustomerId($customerId);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(new Phrase($e->getMessage()));
            }
        }

        return $proceed($entity);
    }

    /**
     * @param \Magento\SalesRule\Api\Data\CouponInterface $coupon
     * @return this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addCustomerToCoupon(CouponInterface $coupon)
    {
        $extensionAttributes = $coupon->getExtensionAttributes();
        if ($extensionAttributes == null) {
            $extensionAttributes = $this->couponExtensionFactory->create();
        }

        if ($customerId = $coupon->getCouponCustomerId()) {
            try {
                $customerEmail = $this->couponHelper->getCustomerEmail($customerId);
            } catch (\Exception $e) {
                $customerEmail = '';
            }
            $extensionAttributes->setCouponCustomerId($customerEmail);
            $coupon->setExtensionAttributes($extensionAttributes);
        }
        return $this;
    }
}
