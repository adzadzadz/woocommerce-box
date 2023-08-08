<?php 

namespace WCB;

class Box {
    private String $size;

    private Int $max_point_value = 0;

    private Int $current_point_value = 0;

    private Int $status = 0;

    private Int $quantity = 1;
    
    private Array $items = [];

    public function __construct($size, $max_point_value)
    {
        $this->size = $size;
        $this->max_point_value = $max_point_value;
    }

    public function get_max_point_value()
    {
        return $this->max_point_value * $this->quantity;
    }

    public function get_props($props)
    {
        $box = [];
        foreach ($props as $prop) {
            if ($prop == 'max_point_value')
                $box[$prop] = $this->get_max_point_value();
            else
                $box[$prop] = $this->$prop;
        }
        return $box;
    }

    public function get_size()
    {
        return $this->size;
    }

    public function set_quantity(Int $quantity)
    {
        $this->quantity = $quantity;
        return $this;
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