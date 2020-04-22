<?php

namespace Sendbox\Sendboxshipping\Model\System\Config\Source;

class Pickup implements \Magento\Framework\Option\ArrayInterface
{ 
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    { 
     
        return [
            'value' => 'pick_up',
            'value' => 'drop_off'
           
        ];
    }
}