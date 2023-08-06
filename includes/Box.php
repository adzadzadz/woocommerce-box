<?php 

namespace WCB;

class Box {
    private $size;

    private $max_point_value;

    private $current_point_value = 0;

    private $status = 0;

    private Int $quantity = 1;
    
    private $items = [];

    public function __construct($size, $max_point_value)
    {
        $this->size = $size;
        $this->max_point_value = $max_point_value;
    }

    public function get_max_point_value()
    {
        return $this->max_point_value;
    }

    public function get_size()
    {
        return $this->size;
    }

    public function set_quantity(Int $quantity)
    {
        $this->max_point_value = $quantity * $this->get_max_point_value();
        $this->quantity = $quantity;
    }

    public function get_quantity()
    {
        return $this->quantity;
    }

    public function add_item($item)
    {
        $this->items[] = $item;
        $this->current_point_value += $item->get_point_value();
        $this->update_status();
    }

    public function get_status()
    {
        return $this->status;
    }

    public function set_items($items)
    {
        $this->items = $items;
    }
    public function get_items()
    {
        return $this->items;
    }

    public function set_current_point_value($current_point_value)
    {
        $this->current_point_value = $current_point_value;
    }
    
    public function get_current_point_value()
    {
        return $this->current_point_value;
    }

    public function update_status()
    {
        if ($this->current_point_value == 0) {
            $this->status = 'empty';
        } elseif ($this->current_point_value < $this->max_point_value) {
            $this->status = 'not-full';
        } elseif ($this->current_point_value == $this->max_point_value) {
            $this->status = 'full';
        } elseif ($this->current_point_value > $this->max_point_value) {
            $this->status = 'over-full';
        }
    }

}