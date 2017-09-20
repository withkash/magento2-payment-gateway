<?php
namespace Kash\Gateway\Controller;

abstract class Action extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Kash\Gateway\Helper\GatewayHelper
     */
    protected $gatewayHelper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Kash\Gateway\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote = null;

    /**
     * Action constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {

        parent::__construct(
            $context
        );

        $this->gatewayHelper = $context->getGatewayHelper();
        $this->curl = $context->getCurl();
        $this->config = $context->getConfig();
        $this->checkoutSession = $context->getCheckoutSession();
        $this->customerSession = $context->getCustomerSession();
    }

    public abstract function execute();
    /**
     * @param string $url
     */
    public function redirect($url) 
    {
        $this->_redirect($url);
    }



    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }
}
