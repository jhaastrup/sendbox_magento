<?php

namespace Sendbox\Sendboxshipping\Block\System\Config;


use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;

class Connectbtn extends \Magento\Config\Block\System\Config\Form\Field {

    /**
     * Path to block template
     */
    const Sendbox_Sendboxshipping = 'system/config/connectbtn.phtml';

    public function __construct(Context $context,
                                $data = array())
    {
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::Sendbox_Sendboxshipping);
        }
        return $this;
    }

    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'url' => $this->getUrl(),
                'html_id' => $element->getHtmlId(),
            ]
        );

        return $this->_toHtml();
    }

   /*  public function getUrl()
    {
        return "url"; //This is your real url you want to redirect when click on button
    } */

} 