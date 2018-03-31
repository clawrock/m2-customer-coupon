<?php

namespace ClawRock\CustomerCoupon\Controller\Adminhtml\Coupon;

class Update extends \Magento\Backend\App\Action
{
    const ACTION_ROUTE = 'clawrock_customer/coupon/update';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \ClawRock\CustomerCoupon\Helper\Coupon
     */
    protected $couponHelper;

    /**
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \ClawRock\CustomerCoupon\Helper\Coupon $couponHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \ClawRock\CustomerCoupon\Helper\Coupon $couponHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->couponHelper = $couponHelper;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $response = [];
        if ($this->getRequest()->isAjax()) {
            $data = $this->getRequest()->getPost();
            try {
                $coupon = $this->couponHelper->loadCoupon($data['couponId']);
                $rule = $this->couponHelper->loadRule($coupon->getRuleId());
                if ($data['remove']) {
                    $customerId = null;
                    $response['message'] = 'Customer has been removed from coupon.';
                } else {
                    $response['message'] = 'Customer has been assigned to the coupon.';
                }
                $customerId = $this->couponHelper->prepareCustomerIdByEmail(
                    $data['customerEmail'],
                    $rule->getWebsiteIds(),
                    (bool)$data['remove']
                );
                $coupon->setCouponCustomerId($customerId)->save();
            } catch (\Exception $e) {
                $response['error'] = 1;
                $response['message'] = $e->getMessage();
            }
        }
        return $result->setData($response);
    }
}
