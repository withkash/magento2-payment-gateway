<?php

namespace Kash\Gateway\Controller;

class Context extends \Magento\Framework\App\Action\Context
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

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Kash\Gateway\Helper\GatewayHelper $gatewayHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Kash\Gateway\Model\Config $config,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct(
            $request,
            $response,
            $objectManager,
            $eventManager,
            $url,
            $redirect,
            $actionFlag,
            $view,
            $messageManager,
            $resultRedirectFactory,
            $resultFactory
        );
        $this->gatewayHelper = $gatewayHelper;
        $this->curl = $curl;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Kash\Gateway\Helper\GatewayHelper
     */
    public function getGatewayHelper() 
    {
        return $this->gatewayHelper;
    }

    /**
     * @return \Magento\Framework\HTTP\Client\Curl
     */
    public function getCurl() 
    {
        return $this->curl;
    }

    /**
     * @return \Kash\Gateway\Model\Config
     */
    public function getConfig() 
    {
        return $this->config;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession() 
    {
        return $this->checkoutSession;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession() 
    {
        return $this->customerSession;
    }
}
