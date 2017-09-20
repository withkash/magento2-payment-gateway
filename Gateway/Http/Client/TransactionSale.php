<?php
namespace Kash\Gateway\Gateway\Http\Client;

use Kash\Gateway\Gateway\Request\PaymentDataBuilder;
use Kash\Gateway\Helper\GatewayHelper;
use Kash\Gateway\Model\Config;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Phrase;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Service\InvoiceService;

class TransactionSale extends AbstractTransaction
{
    const TRANSACTION_AUTHORIZED = 'AUTHORIZED';
    const TRANSACTION_DECLINED = 'DECLINED';
    const TRANSACTION_PROCESSING = 'PROCESSING';
    const TRANSACTION_CANCELLED = 'CANCELLED';
    const TRANSACTION_REFUNDED = 'REFUNDED';
    // a tenth of a second
    const RETRY_DELAY_MICROSEONDS = 100000;

    /**
     * @var Curl
     */
    protected $curl;
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    public function __construct(
        GatewayHelper $gatewayHelper,
        Session $checkoutSession,
        Config $config,
        Curl $curl,
        OrderSender $orderSender,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->curl = $curl;
        parent::__construct($gatewayHelper);
    }

    /**
     * Process HTTP request
     *
     * @param  array $data
     * @return null|\stdClass
     * @throws LocalizedException
     */
    protected function process(array $data)
    {
        $logger = $this->gatewayHelper->logger();
        $customerToken = $this->checkoutSession->getKashCustomerToken();
        $payment = $data[PaymentDataBuilder::PAYMENT];
        $order = $payment->getOrder();

        $key = $this->gatewayHelper->getKey();
        $url = $this->gatewayHelper->getURL();
        $email = $order->getCustomerEmail();

        $billingAddress = $order->getBillingAddress();

        $data = $this->gatewayHelper->buildDataObject($email, $billingAddress);
        $data['customer_id'] = $customerToken;
        $data['amount'] = $order->getGrandTotal() * 100;
        $headers = array('request-id' => $this->guidv4());

        $this->curl->setHeaders($headers);
        $this->curl->setCredentials($key, "");
        $logger->log("posting to: ".$url."/transactions");
        $this->curl->post($url."/transactions", $data);
        $response = json_decode($this->curl->getBody());
        // 303 means we've sucessfully created a transaction, still need to check for status
        if ($this->curl->getStatus() === 303) {
            $location = explode('/', $this->curl->getHeaders()['location']);
            $transactionId = end($location);
            $this->curl->get($url.'/transactions/'.$transactionId);
            if ($this->curl->getStatus() === 200) {
                $response = json_decode($this->curl->getBody());
                $payment->setData('x_gateway_reference', $transactionId);
                $payment->setTransactionId($transactionId);
                $this->setAdditionalInformation($order, $transactionId);
                $order->getResource()->save($order);
                $this->completeTransaction($response->status, $order, $transactionId);
            } else {
                throw new LocalizedException(new Phrase("Could not load Kash transaction"));
            }
        } else {
            // throw an error, this will stop magento from creating the order and show the customer an error
            throw new LocalizedException(new Phrase("Could not create Kash transaction"));
        }
    }

    /**
     * @param string $status
     * @param Order  $order
     * @param string $transactionId
     * @throws        LocalizedException
     */
    protected function completeTransaction($status, $order, $transactionId)
    {
        $url = $this->gatewayHelper->getURL().'/transactions/'.$transactionId;

        while ($status === self::TRANSACTION_PROCESSING) {
            usleep(self::RETRY_DELAY_MICROSEONDS);
            $this->curl->get($url);
            $status = json_decode($this->curl->getBody())->status;
        }


        if ($status === self::TRANSACTION_AUTHORIZED) {
            // success status
            return;
        } else {
            // failure status
            throw new LocalizedException(new Phrase("Could not complete Kash transaction"));
        }
    }

    /**
     * Generates UUID
     * See:https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
     *
     * @return string
     */
    protected function guidv4()
    {
        if ((float) phpversion() >= 7.0) {
            $data = random_bytes(16);
        } else {
            $data = openssl_random_pseudo_bytes(16);
        }



        $data = str_split(bin2hex($data), 4);
        $data[3] = dechex(hexdec($data[3]) & 0x0fff | 0x4000); // set version
        $data[4] = dechex(hexdec($data[4]) & 0x3fff | 0x8000); // set the highest bits to 01

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', $data);
    }

    /**
     * @param Order $payment
     */
    public function setAdditionalInformation($order, $kashTransactionId)
    {
        $payment = $order->getPayment();

        $data = array(
            'Gateway Reference' => $kashTransactionId,
            'Amount' => number_format($order->getGrandTotal(), 2),
            'Test Mode' => $this->config->test_mode ? "Yes" : "No"
        );

        $payment->setAdditionalInformation($data);
    }
}
