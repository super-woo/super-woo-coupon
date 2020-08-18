# ToDo

---

- customly validate coupons
- take action before & after default coupons function
- add custom discount type [ "Admin" ]
- add custom filters [ "Admin" ]
- add custom discounts [ "Admin" ]
- add custom rules [ "Admin" ]



# Admin Hooks

---

## wac_discount_type

> Ajax.php:72

These Filters Hooks can be used for filter discount type fields options ;

---

## wac_filters

> Ajax.php:116

These Filters Hooks can be used to add custom filters ;

---




# Front Hooks

---

## wac_brefore_wp_loaded

> Wac_auto.php:31

These Action Hooks will be run before wac_first_order & wac_auto_coupon ;

---

## wac_after_wp_loaded

> Wac_auto.php:34

These Action Hooks will be run after wac_first_order & wac_auto_coupon ;

---

## wac_validator

> Validator.php:23

These Filter Hooks can be use for validate coupons. return True & False ;

---
