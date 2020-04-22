<?php /**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sendbox\Sendboxshipping\Controller\Page;
class Sendbox extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
       \Magento\Framework\App\Action\Context $context,
       \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
{
       $this->resultJsonFactory = $resultJsonFactory;
       parent::__construct($context);
}
    /**
     * View  page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {  
      $server_obj = ($_SERVER);
     $get_state = ($_GET["state"]);
    $get_code = ($_GET["code"]);

    $new_url = $get_state."&code=" .$get_code;
     
      header('Location:'.$new_url);
      die();
       
      // echo 'nah man you be';
       //$result = $this->resultJsonFactory->create();
      //$data =$_SERVER['HTTP_HOST']; //['message' => 'Hello world!'];

//return $result->setData($data);
} 

}
