<?php
namespace Kash\Gateway\Observer;

use Magento\Framework\Event\ObserverInterface;

class LogQuoteToOrder implements ObserverInterface
{

    /**
     * @var \Kash\Gateway\Helper\GatewayHelper
     */
    protected $gatewayHelper;

    /**
     * LogQuoteToOrder constructor.
     *
     * @param \Kash\Gateway\Helper\GatewayHelper $gatewayHelper
     * @param array $data
     */
    public function __construct(
        \Kash\Gateway\Helper\GatewayHelper $gatewayHelper,
        array $data = []
    ) {
        $this->gatewayHelper = $gatewayHelper;
    }

    
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();
        $logger = $this->gatewayHelper->logger();
        $logger->log('quote '.$quote->getId().': was converted to order '.$order->getIncrementId());
    }
}