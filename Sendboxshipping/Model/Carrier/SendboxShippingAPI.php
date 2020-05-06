<?php

namespace Sendbox\Sendboxshipping\Model\Carrier;



class SendboxShippingAPI{
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
            throw new \Exception('Error:'. curl_error($ch));
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


