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

     $get_params = $this->getRequest()->getParams();
     $state = $get_params['state'];
     $code = $get_params['code'];

    $new_url = $state."&code=" .$code;
   // echo 'nah man you be';

    $this->_redirect($new_url);

     
     // header('Location:'.$new_url);
      //die();
       
} 

}
