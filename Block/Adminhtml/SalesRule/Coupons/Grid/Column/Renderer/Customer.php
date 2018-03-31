<?php

namespace ClawRock\CustomerCoupon\Block\Adminhtml\SalesRule\Coupons\Grid\Column\Renderer;

use Magento\Framework\Phrase;

class Customer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * @param  \Magento\Framework\DataObject
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = parent::render($row);
        $html .= '<div class="clawrock-customer-coupon"><button class="clawrock-coupon-customer-update-button"';
        $html .= 'onclick=\'updateCoupon('.$row->getId().', "'.$this->getUpdateUrl().'", 0); return false\'>';
        $html .= new Phrase('Update').'</button>';
        $html .= '<button class="clawrock-coupon-customer-remove-button"';
        $html .= 'onclick=\'updateCoupon('.$row->getId().', "'.$this->getUpdateUrl().'", 1); return false\'>';
        $html .= new Phrase('Remove').'</button>';
        $html .= '<p class="clawrock-coupon-customer-message"></p></div>';

        return $html;
    }

    /**
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('clawrock_customer/coupon/update');
    }
}
