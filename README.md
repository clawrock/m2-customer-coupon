# Customer Coupon

Module provides enhancements to sales rules, such as:
- search for all coupons assigned to rule (Magento 2.2+ required)
- free shipping for specified methods
- coupon assigned directly to customer, visible on customer account

## Requirements
1) Magento 2.1+

## Installation (using composer)
1. `composer require clawrock/m2-customer-coupon`
2. `php bin/magento setup:upgrade`

## Installation (manually)
1. Clone the repository to `app/code/ClawRock/CustomerCoupon`
2. `php bin/magento setup:upgrade`

### Configuration
1. Go to `Stores -> Configuration -> ClawRock -> Customer Coupon`
2. You can modify message that will be displayed in customer account when he is lack of coupons assigned.

### Free shipping for specified methods
1. Go to `Marketing -> Cart Price Rules`, select rule or create new one,
2. IMPORATNT! To make this option works, you have to select `Free shipping - for shipment with matching items` in `Actions` tab,
3. Select methods that you want to be free in `Apply free shipping to` tab,

### Assign coupon to customer
1. Go to `Marketing -> Cart Price Rules`, select rule or create new one,
2. If you have specific coupon, add customer email to `Coupon assigned to customer` to assign it,
3. You can also assign customer to auto generated coupons, just go to `Manage Coupon Codes grid` and type customer email in input box.

## Tests
To run test run from console: `vendor/phpunit/phpunit/phpunit -c phpunit.xml.dist`
