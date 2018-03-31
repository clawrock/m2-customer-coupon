<?php

namespace ClawRock\CustomerCoupon\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Coupon extends AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context     $context
     * @param \Magento\Customer\Model\CustomerFactory   $customerFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory
    ) {
        parent::__construct($context);
        $this->customerFactory = $customerFactory;
        $this->resourceConnection = $resourceConnection;
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param  string $customerEmail
     * @param  array  $websiteIds
     * @return \Magento\Customer\Model\Customer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerByEmail($customerEmail, $websiteIds = [])
    {
        $connection = $this->resourceConnection->getConnection();

        $bind = ['customer_email' => $customerEmail];
        $select = $connection->select()->from(
            $connection->getTableName('customer_entity'),
            ['entity_id']
        )->where(
            'email = :customer_email'
        );

        if ($this->customerFactory->create()->getSharingConfig()->isWebsiteScope()) {
            $bind['website_id'] = implode(', ', $websiteIds);
            $select->where('website_id IN(:website_id)');
        }

        $customerId = $connection->fetchOne($select, $bind);
        if (!$customerId) {
            throw new LocalizedException(new Phrase('Customer with given email address doesn\'t exists'));
        }
        $customer = $this->customerFactory->create()->load($customerId);
        return $customer;
    }

    /**
     * @param  string  $customerEmail
     * @param  array   $websiteIds
     * @param  boolean $remove
     * @return null|int
     */
    public function prepareCustomerIdByEmail($customerEmail, $websiteIds = [], $remove = false)
    {
        if ($remove) {
            return null;
        }
        $customer = $this->getCustomerByEmail($customerEmail, $websiteIds);
        return $customer->getId();
    }

    /**
     * @param  string $code
     * @return \Magento\SalesRule\Model\Coupon
     * @throws  \Magento\Framework\Exception\LocalizedException
     */
    public function loadCouponByCode($code)
    {
        $coupon = $this->couponFactory->create()->loadByCode($code);
        if (!$coupon->getId()) {
            throw new LocalizedException(new Phrase("Coupon with given coupon doesn't exists."));
        }
        return $coupon;
    }

    /**
     * @param  int $couponId
     * @return \Magento\SalesRule\Model\Coupon
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadCoupon($couponId)
    {
        $coupon = $this->couponFactory->create()->load($couponId);
        if (!$coupon->getId()) {
            throw new LocalizedException(new Phrase("Coupon with given id doesn't exists."));
        }
        return $coupon;
    }

    /**
     * @param  int $ruleId
     * @return \Magento\SalesRule\Model\Rule
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadRule($ruleId)
    {
        $rule = $this->ruleFactory->create()->load($ruleId);
        if (!$rule->getId()) {
            throw new LocalizedException(new Phrase("Rule doesn't exists."));
        }
        return $rule;
    }


    /**
     * @param  int $customerId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerEmail($customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        if (!$customer->getId()) {
            throw new LocalizedException(new Phrase("Customer doesn't exists."));
        }
        return $customer->getEmail();
    }
}
