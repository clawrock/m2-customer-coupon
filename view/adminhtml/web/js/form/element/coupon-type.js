define([
    'underscore',
    'uiRegistry',
    'Magento_SalesRule/js/form/element/coupon-type'
], function (_, uiRegistry, coupon) {
    'use strict';

    return coupon.extend({
        initialize: function () {
            this._super();
            this.customerCoupon = uiRegistry.get('index = customer_coupon');
            this.autoGeneration = uiRegistry.get('index = use_auto_generation');
            this.displayCustomerCoupon();
        },

        onUpdate: function () {
            this.displayCustomerCoupon();
            return this._super();
        },

        displayCustomerCoupon: function () {
            if (this.autoGeneration == undefined) {
                this.customerCoupon.hide();
                this.customerCoupon.disable();
                return;
            }
            if (this.value() == 2 && this.autoGeneration.value() == 0) {
                this.customerCoupon.enable();
                this.customerCoupon.show();
            } else {
                this.customerCoupon.hide();
                this.customerCoupon.disable();
            }
        }
    });
});
