<?php

namespace Kash\Gateway\Gateway\Http\Client;

use Kash\Gateway\Helper\GatewayHelper;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractTransaction
 */
abstract class AbstractTransaction implements ClientInterface
{
    /**
     * @var GatewayHelper
     */
    protected $gatewayHelper;

    /**
     * AbstractTransaction constructor.
     * @param GatewayHelper $gatewayHelper
     */
    public function __construct(GatewayHelper $gatewayHelper)
    {
        $this->gatewayHelper = $gatewayHelper;
    }

    /**
     * @param TransferInterface $transferObject
     * @return mixed
     * @throws ClientException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $logger = $this->gatewayHelper->logger();
        $data = $transferObject->getBody();
        $response['object'] = [];

        try {
            $response['object'] = $this->process($data);
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            $logger->log($message);
            throw new ClientException($message);
        } finally {
            $log['response'] = (array) $response['object'];
            $logger->log(var_export($log, true));
        }

        return $response;
    }

    /**
     * Process http request
     * @param array $data
     * @return void
     */
    abstract protected function process(array $data);
}
