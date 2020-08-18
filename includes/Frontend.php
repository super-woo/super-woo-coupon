<?php

namespace WpAdroit\Wac_Coupon;

use WpAdroit\Wac_Coupon\Frontend\Wac_auto;
use WpAdroit\Wac_Coupon\Frontend\Wac_front;
use WpAdroit\Wac_Coupon\Frontend\Wac_url;

/**
 * Frontend handler class
 */
class Frontend
{
    /**
     * Frontend constructor.
     */
    public function __construct()
    {
        new Wac_front;
        new Wac_url;
        new Wac_auto;
    }
}
