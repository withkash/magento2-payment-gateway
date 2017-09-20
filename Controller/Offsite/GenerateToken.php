<?php
namespace Kash\Gateway\Controller\Offsite;

use Kash\Gateway\Controller\Action;
use \Kash\Gateway\Controller\Context;
use \Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class GenerateToken extends Action
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
        $this->curl->setCredentials($key, "");
        $email = $this->getRequest()->getPostValue("email");
        $data = $this->gatewayHelper->buildDataObject($email);
        $data['context_id'] = $this->getQuote()->getId();
        $logger->log("posting to: ".$url."/account-setup-token");
        $this->curl->post($url."/account-setup-token", $data);
        $response = $this->curl->getBody();
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(json_decode($response));
        return $result;
    }
}
