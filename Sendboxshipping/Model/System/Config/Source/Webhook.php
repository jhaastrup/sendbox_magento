<?php

namespace Sendbox\Sendboxshipping\Model\System\Config\Source;

class Webhook implements \Magento\Framework\Option\ArrayInterface
{ 
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray()
    { 
     $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://';
      $server_obj = $_SERVER['PHP_SELF'];
      $domain = $_SERVER['HTTP_HOST'];
      $static_url = $protocol.$domain.$server_obj."/webhook/page/sendbox";
        return [
            'value' => $static_url
           
        ];
    }
}