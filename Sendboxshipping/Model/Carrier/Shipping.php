<?php

namespace Sendbox\Sendboxshipping\Model\Carrier;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;



class Sendbox_Shipping_API{
    //make a post to sendbox api using curl.
    public function post_on_api_by_curl($url, $data, $api_key)
    {
        $ch = curl_init($url);
        // Setup request to send json via POST.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:' . $api_key));
        // Return response instead of printing.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Send request.
        $result = curl_exec($ch);
        curl_close($ch);
        // Print response.
        return $result;
    }
      //make a get request using curl to sendbox

      public function get_api_response_by_curl($url)
      {
          $handle = curl_init();
          curl_setopt($handle, CURLOPT_URL, $url);
          curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
          $output = curl_exec($handle);
          curl_close($handle);
          return $output;
      } 

        //make request to sendbox with header

    public function get_api_response_with_header($url, $request_headers){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        $season_data = curl_exec($ch);
        if (curl_errno($ch)) {
          print "Error: " . curl_error($ch);
            exit();
        }
        // Show me the result
        curl_close($ch);
        return $season_data;

    } 

     //all sendbox endpoints
     public function get_sendbox_api_url($url_type)
     {
         if ('delivery_quote' == $url_type) {
             $url = 'https://api.sendbox.ng/v1/merchant/shipments/delivery_quote';
         } elseif ('countries' == $url_type) {
             $url = 'https://api.sendbox.co/auth/countries?page_by={' . '"per_page"' . ':264}';
         }
         elseif('city' == $url_type){
             $url = 'https://api.sendbox.co/auth/cities?page_by=&filter_by={"state_code":"LOS"}';
         }
          elseif ('states' == $url_type) {
             $url = 'https://api.sendbox.co/auth/states?page_by={' . '"per_page"' . ':264}&filter_by={'.'"country_code"'.':"NG"'.'}';
         } elseif ('shipments' == $url_type) {
             $url = 'https://api.sendbox.ng/v1/merchant/shipments';
         } elseif ('item_type' == $url_type) {
             $url = 'https://api.sendbox.ng/v1/item_types';
         } elseif ('profile' == $url_type) {
             $url = 'https://api.sendbox.co/oauth/profile';
         }else {
             $url = '';
         }
         return $url;
     }
}


class Shipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'sendboxshipping';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;
    protected $_checkoutSession;
    //protected $zendClient;


    /**
     * Shipping constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
       // \Zend\Http\Client $zendClient,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_checkoutSession = $checkoutSession;
        //$this->zendClient = $zendClient;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * get allowed methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
    /**
     * @return float
     */
    private function getShippingPrice()
    {
        $sendbox_obj = new Sendbox_Shipping_API();
        $origin_country = "Nigeria";
        $origin_state = $this->getConfigData('state');
        $destination_country = $this->_checkoutSession->getQuote()->getShippingAddress()->getCountry();
        $destination_state = $this->_checkoutSession->getQuote()->getShippingAddress()->getRegion();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName("sendbox");

        $sql = "SELECT * FROM " . $tableName;
        $result = $connection->fetchAll($sql);
        foreach( $result as $value ){
         $api_key = $value['auth_token'];
        }
        //$api_key = $result[0]->auth_token;

        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $items = $cart->getQuote()->getAllItems();
        $weight = 0;
        foreach($items as $item) {
        $weight += ($item->getWeight() * $item->getQty()) ;      
         }  
         $url = $sendbox_obj->get_sendbox_api_url('delivery_quote');
         $params = array(
            'origin_country' => $origin_country,
            'origin_state' => $origin_state,
            'destination_country_code'=>$destination_country,
            'destination_state' => $destination_state,
            'weight' => $weight
         ); 

         $payload = json_encode($params);
 
       // Prepare new cURL resource
       $ch = curl_init('https://live.sendbox.co/shipping/shipment_delivery_quote');
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLINFO_HEADER_OUT, true);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
 
      // Set HTTP Header for POST request 
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Authorization:'.$api_key)
      );
 
    // Submit the POST request
           $result = curl_exec($ch);
           $sendbox_quotes = json_decode($result, true);

 
         // Close cURL session handle
            curl_close($ch);

            //check for max_quoted_fee
            if (in_array("max_quoted_fee", $sendbox_quotes)){
                $shippingPrice = $sendbox_quotes['max_quoted_fee']; 
            }
            return $shippingPrice;

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/bob.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info(gettype($sendbox_quotes));

         

            
         
           /*  $this->zendClient->reset();
            $this->zendClient->setUri($url);
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
       	    $this->zendClient->setHeaders([
                'Content-Type' => 'Application/json',
               
                'Authorization' => 'Bearer'.$api_key,
       	    ]);
       	    $this->zendClient->setParameterPost([
                'origin_country' => $origin_country,
                'origin_state' => $origin_state,
                'destination_country'=>$destination_country,
                'destination_state' => $destination_state,
                'weight' => $weight
            ]);
       	    $this->zendClient->send();
            $response = $this->zendClient->getResponse(); */

    
       

        // $payload_data = new stdClass();
         //$payload_data->destination_state= $destination_state;
			//$payload_data->destination_country = $destination_country;
			//$payload_data->origin_country = $origin_country;
			//$payload_data->origin_state = $origin_state;
            //$payload_data->weight = $weight;
            //$quotes_data = json_encode($payload_data);
         
        // $session_name = $this->_checkoutSession->getQuote()->getShippingAddress()->getFirstName();
         //$session_last_name = $this->_checkoutSession->getQuote()->getShippingAddress()->getLastName();
         //$session_city = $this->_checkoutSession->getQuote()->getShippingAddress()->getCity();
         //$session_country = $this->_checkoutSession->getQuote()->getShippingAddress()->getCountry();
         //$session_state = $this->_checkoutSession->getQuote()->getShippingAddress()->getRegion();
         //$session_address = $this->_checkoutSession->getQuote()->getShippingAddress()->getStreet();
         //$session_telephone = $this->_checkoutSession->getQuote()->getShippingAddress()->getTelephone();
         //$session_addr = $session_address[0];

      /*   $configPrice = $this->getConfigData('price');

        $shippingPrice = $this->getFinalPriceWithHandlingFee($configPrice);
        return $shippingPrice; */
    }

   

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));   

        $amount = $this->getShippingPrice();

        $method->setPrice($amount);
        $method->setCost($amount);

        $result->append($method);

        return $result;
    }
}