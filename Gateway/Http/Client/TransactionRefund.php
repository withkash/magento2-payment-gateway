<?php
namespace Kash\Gateway\Gateway\Http\Client;

use Kash\Gateway\Gateway\Request\PaymentDataBuilder;
use Kash\Gateway\Helper\GatewayHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\HTTP\Client\Curl;

class TransactionRefund extends AbstractTransaction
{
    /**
     * @var Curl
     */
    protected $curl;

    public function __construct(
        GatewayHelper $gatewayHelper,
        Curl $curl
    ) {
        $this->curl = $curl;
        parent::__construct($gatewayHelper);
    }

    /**
     * Process HTTP request
     * @param array $data
     * @return null|\stdClass
     * @throws \Exception
     */
    protected function process(array $data)
    {
        $kashTransactionId = $data[PaymentDataBuilder::PAYMENT]->getCreditmemo()->getInvoice()->getTransactionId();
        $logger = $this->gatewayHelper->logger();

        $refundAmount = $data[PaymentDataBuilder::AMOUNT];
        $logger->log('refunding $' . $refundAmount . ' for ' . $kashTransactionId);
        $config = $this->gatewayHelper->config();
        $serverKey = $config->server_key;

        $url = $config->api_url.'/refunds';

        $requestPayload = array(
            'amount' => $refundAmount * 100,
            'transaction_id' => $kashTransactionId
        );

        $this->curl->setCredentials($serverKey, '');
        $this->curl->post($url, $requestPayload);
        $statusCode = $this->curl->getStatus();
        if ($statusCode === 303) {
            $location = explode('/', $this->curl->getHeaders()['location']);
            $refundId = end($location);
            $url = $config->api_url.'/refunds/'.$refundId;
            $this->curl->get($url);
            $statusCode = $this->curl->getStatus();
            if ($statusCode === 200) {
                $result = new \stdClass();
                $result->body = json_decode($this->curl->getBody());
                $result->header = $this->curl->getHeaders();
                $result->statusCode = $this->curl->getStatus();

                return $result;
            } else {
                $this->throwError();
            }
        } else {
            $this->throwError();
        }
    }

    protected function throwError()
    {
        $message = json_decode($this->curl->getBody())->message;
        throw new LocalizedException($message);
    }


}
