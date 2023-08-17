<?php 

namespace WCB;

// TODO Add rule control
// TODO Rule: Minimum spending 200usd
// TODO Rule: All boxes at least 80% full in order to get free shipping
// TODO Rule: Flat amount per box large 50usd, 30usd.
class Box_Rules 
{
    public $currency = 'usd';

    public $min_spending = 200;

    public $min_box_fill_rate = 80;
    
    public function __construct($config)
    {
        $this->set_currency($config['currency']);
        $this->set_min_spending($config['min_spending']);
        $this->set_min_box_fill_rate($config['min_box_fill_rate']);
    }

    public function set_currency($currency)
    {
        $this->currency = $currency;
    }

    public function set_min_spending($min_spending)
    {
        $this->min_spending = $min_spending;
    }

    public function set_min_box_fill_rate($min_box_fill_rate)
    {
        $this->min_box_fill_rate = $min_box_fill_rate;
    }

    public function is_min_spent_reached()
    {
        if ($this->min_spending <= WC()->cart->total) {
            return true;
        }
        return false;
    }

    public function is_min_box_fill_rate_reached($current_fill_rate)
    {
        if ($current_fill_rate >= $this->min_box_fill_rate) {
            return true;
        }
        return false;
    }
}