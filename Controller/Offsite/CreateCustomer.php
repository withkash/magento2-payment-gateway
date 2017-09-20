<?php
namespace Kash\Gateway\Controller\Offsite;

use Kash\Gateway\Controller\Action;
use Kash\Gateway\Controller\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class CreateCustomer extends Action
{
    public function __construct(
        Context $context
    ) {


        parent::__construct(
            $context
        );
    }

    /**
     * @return ResultInterface
     */
    public function execute() {
        $logger = $this->gatewayHelper->logger();
        $url = $this->gatewayHelper->getURL();
        $key = $this->gatewayHelper->getKey();
        $data = $this->getRequest()->getPostValue();
        $data["context_id"] = $this->getQuote()->getId();
        $this->curl->setCredentials($key, "");
        $logger->log("posting to: ".$url."/customers");
        $this->curl->post($url."/customers", $data);
        if ($this->curl->getStatus() === 303) {
            $location = explode('/',$this->curl->getHeaders()['location']);
            $customerId = end($location);
            $this->curl->get($url.'/customers/'.$customerId);
            // success
            if ($this->curl->getStatus() === 200) {
                $response = json_decode($this->curl->getBody());
                $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $result->setData($response);
                $this->checkoutSession->setKashCustomerToken($response->id);
                return $result;
            } else {
                $err = "Failed to load customer";
                $logger->log($err);
                throw new LocalizedException(new Phrase($err));
            }
        } else {
            $err = "Failed to create customer";
            $logger->log($err);
            throw new LocalizedException(new Phrase($err));
        }
        return;
    }
}
