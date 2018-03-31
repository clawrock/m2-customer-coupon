define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/single-checkbox'
], function (_, uiRegistry, checkbox) {
    'use strict';

    return checkbox.extend({
        initialize: function () {
            this._super();
            this.customerCoupon = uiRegistry.get('index = customer_coupon');
            this.displayCustomerCoupon();
        },

        onUpdate: function () {
            this.displayCustomerCoupon();
            return this._super();
        },

        displayCustomerCoupon: function () {
            this.couponType = uiRegistry.get('index = coupon_type');
            if (this.couponType == undefined) {
                this.customerCoupon.hide();
                this.customerCoupon.disable();
                return;
            }
            if (this.value() == 0 && this.couponType.value() == 2) {
                this.customerCoupon.enable();
                this.customerCoupon.show();
            } else {
                this.customerCoupon.hide();
                this.customerCoupon.disable();
            }
        }
    });
});
