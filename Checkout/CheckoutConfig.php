<?php

namespace Kash\Gateway\Checkout;

class CheckoutConfig implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const CODE = 'kash_gateway';

    /**
     * @var \Kash\Gateway\Model\Config
     */
    private $_config;

    /**
     * @var \Kash\Gateway\Model\Offsite
     */
    private $_adapter;


    /**
     * CheckoutConfig constructor.
     * @param \Kash\Gateway\Model\Config $config
     * @param \Kash\Gateway\Model\Offsite $adapter
     */
    public function __construct(
        \Kash\Gateway\Model\Config $config,
        \Kash\Gateway\Model\Offsite $adapter
    ) {
        $this->_config = $config;
        $this->_adapter = $adapter;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => true
                ]
            ]
        ];
    }
}