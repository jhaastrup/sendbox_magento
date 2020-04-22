<?php
namespace Sendbox\Sendboxshipping\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getConfig($path='')
    {
        if($path) return $this->scopeConfig->getValue( $path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        return $this->scopeConfig;
    }
}