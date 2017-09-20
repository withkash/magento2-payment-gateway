<?php
namespace Kash\Gateway\Observer;

use Magento\Framework\Event\ObserverInterface;
use OAuth\Common\Http\Client\StreamClient;
use OAuth\Common\Http\Uri\UriFactory;

class SendReport implements ObserverInterface
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Kash\Gateway\Model\Config
     */
    protected $gatewayConfig;

    /**
     * @var \Kash\Gateway\Helper\GatewayHelper
     */
    protected $gatewayHelper;

    /**
     * @var StreamClient
     */
    protected $streamClient;

    /**
     * @var UriFactory
     */
    protected $uriFactory;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Kash\Gateway\Model\Config $gatewayConfig,
        \Kash\Gateway\Helper\GatewayHelper $gatewayHelper,
        StreamClient $streamClient,
        UriFactory $uriFactory,
        array $data = []
    ) {
        $this->salesOrderFactory = $salesOrderFactory;
        $this->gatewayConfig = $gatewayConfig;
        $this->gatewayHelper = $gatewayHelper;
        $this->streamClient = $streamClient;
        $this->uriFactory = $uriFactory;
    }


    public function execute(\Magento\Framework\Event\Observer $observer) 
    {
        $orderId = $observer->getData('order_ids')[0];

        $order = $this->salesOrderFactory->create()->load($orderId);
        $payment = $order->getPayment()->getMethodInstance()->getCode();
        $total = number_format($order->getGrandTotal(), 2);
        $config = $this->gatewayConfig;

        $url = $config->post_url.'/reporting';

        $logger = $this->gatewayHelper->logger();
        $logger->log("order ".$order->getIncrementId()." paid with: ".$payment);
        $log = $logger->getLog();

        $data = array(
            'x_account_id' => $config->x_account_id,
            'x_merchant' => $config->x_shop_name,
            'x_payment' => $payment,
            'x_amount' => $total,
            'x_log' => $log
        );

        $data['x_signature'] = $this->getSignature($data, $config->server_key);

        try {
            $result = $this->streamClient->retrieveResponse($this->uriFactory->createFromAbsolute($url), $data);
            //If the server did not return an error, erase the part of the log we just sent.
            if ($result) {
                $logger->resetLog(strlen($log));
            }
            // file_get_contents may fail on devenv
        } catch (\Exception $e) {
            $logger->log('Failed to report'.$e);
        }
    }

    /**
     * Gateway signing mechanism
     *
     * @param array $request
     * @param $secret_key
     * @return string
     */
    public function getSignature(array $request, $secret_key)
    {
        ksort($request);
        $signature = '';
        foreach ($request as $key => $val) {
            if ($key === 'x_signature' || substr($key, 0, 2) !== "x_") {
                continue;
            }
            $signature .= $key . $val;
        }
        $sig = hash_hmac('sha256', $signature, $secret_key, false);
        return $sig;
    }


}
