<?php

namespace ClawRock\CustomerCoupon\Block\Account;

class Coupon extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory
     */
    protected $couponCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\Collection
     */
    protected $coupons;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Config
     */
    protected $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession,
     * @param \Magento\Framework\App\ResourceConnection $resource,
     * @param \ClawRock\CustomerCoupon\Helper\Config
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ResourceConnection $resource,
        \ClawRock\CustomerCoupon\Helper\Config $config,
        array $data = []
    ) {
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->customerSession = $customerSession;
        $this->resource = $resource;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\SalesRule\Model\ResourceModel\Coupon\Collection
     */
    public function getCustomerCoupons()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->coupons) {
            $this->coupons = $this->couponCollectionFactory->create()
                ->addFieldToSelect([
                    'code',
                    'usage_limit',
                    'times_used'
                ])->join(
                    ['salesrule' => $this->resource->getTableName('salesrule')],
                    'main_table.rule_id = salesrule.rule_id',
                    ['name', 'description']
                )->addFieldToFilter(
                    'coupon_customer_id',
                    $customerId
                )->addFieldToFilter(
                    'is_active',
                    true
                )->setOrder(
                    'created_at',
                    'desc'
                );
        }
        return $this->coupons;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCustomerCoupons()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'customer.coupons.pager'
            )->setCollection(
                $this->getCustomerCoupons()
            );
            $this->setChild('pager', $pager);
            $this->getCustomerCoupons()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomMessage()
    {
        return $this->config->getCustomMessage();
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
