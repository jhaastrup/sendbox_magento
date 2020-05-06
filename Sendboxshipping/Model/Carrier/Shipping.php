<?php

namespace Sendbox\Sendboxshipping\Model\Carrier;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;


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
    protected $_shippingApi;
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
        \Sendbox\Sendboxshipping\Model\Carrier\SendboxShippingAPI $shippingApi,
       // \Zend\Http\Client $zendClient,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_shippingApi = $shippingApi;
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

     //This function checks if auth_token is still valid

     public function checkAuth(){
        $sendbox_obj = $this->_shippingApi;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName("sendbox");
    
            $sql = "SELECT * FROM " . $tableName;
            $result = $connection->fetchAll($sql);
            foreach( $result as $value ){
             $auth_header = $value['auth_token'];
            }
        
    
        $type = "application/json";
    
        $request_headers = array(
            "Content-Type: " .$type,
            "Authorization: " .$auth_header,
        ); 
    
        $profile_url = $sendbox_obj->get_sendbox_api_url('profile');
        $profile_res = $sendbox_obj->get_api_response_with_header($profile_url,$request_headers);
        $profile_obj = json_decode($profile_res);
        //var_dump($profile_obj, property_exists($profile_obj, 'title'));
    
        
        if(isset($profile_obj->title)){
            //make a new request to oauth
        
            $s_url = 'https://live.sendbox.co/oauth/access/access_token/refresh?';
            $app_id =  $this->helper('Sendbox\Sendboxshipping\Helper\Data')->getConfig('carriers/sendboxshipping/appid');
            
            $client_secret =  $this->helper('Sendbox\Sendboxshipping\Helper\Data')->getConfig('carriers/sendboxshipping/clientsecret');
            foreach( $result as $value ){
                $refresh_token = $value['refresh_token'];
               }
            $url_oauth = $s_url.'app_id='.$app_id.'&client_secret='.$client_secret;
            $type = "application/json";
    
            $headers = array(
                "Content-Type: " .$type,
                "Refresh-Token: " .$refresh_token,
            ); 
            //var_dump($url_oauth);
            $oauth_res = $sendbox_obj->get_api_response_with_header($url_oauth,$headers);
            $oauth_obj = json_decode($oauth_res);
            if(isset($oauth_obj->access_token)){
                $new_auth = $oauth_obj->access_token;
            }
            if(isset($oauth_obj->refresh_token)){
                $new_refresh = $oauth_obj->refresh_token;
            }
           
            foreach( $result as $value ){
                $ID = $value['id'];
               } 
    
               $values = "auth_token='".$new_auth."', refresh_token='".$new_refresh."'";
             
               $sql_query = "UPDATE " .$tableName. " SET " . $values . " WHERE id = ". $ID . ";";
               $connection->query($sql_query);
            $auth_header = $new_auth;
        }
        else{
            foreach( $result as $value ){
                $auth_header = $value['auth_token'];
               }
        }
    
        return $auth_header;
          
     }


    private function getShippingPrice()
    {
        $sendbox_obj = $this->_shippingApi;
        $origin_country = "Nigeria";
        $origin_state = $this->getConfigData('state');
        $destination_country = $this->_checkoutSession->getQuote()->getShippingAddress()->getCountry();
        $destination_state = $this->_checkoutSession->getQuote()->getShippingAddress()->getRegion();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName("sendbox");

       /*  $sql = "SELECT * FROM " . $tableName;
        $result = $connection->fetchAll($sql);
        foreach( $result as $value ){
         $api_key = $value['auth_token'];
        } */
        $api_key = $this->checkAuth();

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
           $results = curl_exec($ch);
           $sendbox_quotes = json_decode($results, true);

 
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
            //$logger->info(gettype($sendbox_quotes));
            $logger->info( $this->getShippingPrice());

         

            
         
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