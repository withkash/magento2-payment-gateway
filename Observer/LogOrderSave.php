<?php
namespace Kash\Gateway\Observer;

use Magento\Framework\Event\ObserverInterface;

class LogOrderSave implements ObserverInterface
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Kash\Gateway\Model\ConfigFactory
     */
    protected $gatewayConfigFactory;

    /**
     * @var \Kash\Gateway\Helper\GatewayHelper
     */
    protected $gatewayHelper;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Kash\Gateway\Model\ConfigFactory $gatewayConfigFactory,
        \Kash\Gateway\Helper\GatewayHelper $gatewayHelper,
        array $data = []
    ) {
        $this->salesOrderFactory = $salesOrderFactory;
        $this->gatewayConfigFactory = $gatewayConfigFactory;
        $this->gatewayHelper = $gatewayHelper;
    }


    public function execute(\Magento\Framework\Event\Observer $observer) 
    {
        $order = $observer->getOrder();
        $logger = $this->gatewayHelper->logger();
        $logger->log('order '.$order->getIncrementId().': was saved, state is: '.$order->getState());
    }
}