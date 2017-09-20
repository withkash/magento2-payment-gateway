<?php
namespace Kash\Gateway\Helper;
use Kash\Gateway\Model\Config;
use Kash\Gateway\Model\Logger;
use Magento\Quote\Model\QuoteRepository;


/**
 * This is a special Helper class that gets loaded if Mage::helper() asks only
 * for the namespace. E.g. Mage::helper('kash_gateway')
 *
 * Mage_Core_Model_Config::getHelperClassName() will automatically change the
 * requested 'kash_gateway' to 'kash_gateway/data'.
 */
class GatewayHelper
{

    /**
     * @var \Kash\Gateway\Model\Logger
     */
    protected $gatewayLogger;

    /**
     * @var \Kash\Gateway\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    public function __construct(
        Logger $gatewayLogger,
        Config $config,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        QuoteRepository $quoteRepository
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->gatewayLogger = $gatewayLogger;
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Helper function to retrieve the logger.
     * Use it via `Mage::helper('kash_gateway')->logger()`
     */
    public function logger()
    {
        return $this->gatewayLogger;
    }

    public function config()
    {
        return $this->config;
    }

    /**
     * Builds the data object for
     *
     * @param  string                             $email
     * @param  \Magento\Quote\Model\Quote\Address $billingAddress
     * @return array
     */
    public function buildDataObject($email, $billingAddress = null)
    {
        $isLoggedIn = $this->customerSession->isLoggedIn();
        $customer = $this->customerSession->getCustomer();
        $quote = $this->checkoutSession->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $first = "";
        $last = "";
        if ($isLoggedIn) {
            $first = $customer->getFirstname();
            $last = $customer->getLastname();
        } else if ($billingAddress) {
            $first = $billingAddress->getFirstname();
            $last = $billingAddress->getLastname();
        } else if ($shippingAddress) {
            $first = $shippingAddress->getFirstname();
            $last = $shippingAddress ->getLastname();
        }

        $data = array(
            'customer' => array(
                // use account name if available, otherwise fallback to billing address name and then finally to shipping address name
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email
            ),
            'order' => array(
                'is_registered_account' => $isLoggedIn,
                'created_on' => $quote->getCreatedAt(),
                'last_updated' => $quote->getUpdatedAt(),
                'items' => $this->parseCartData($quote)
            ),
        );
        // on one page checkout we may not have data in our billing address during account-setup-token
        if ($shippingAddress && $shippingAddress->hasData()) {
            $data['shipping_address'] = $this->parseAddressData($shippingAddress);
        }

        if ($billingAddress && $billingAddress->hasData()) {
            $data['billing_address'] = $this->parseAddressData($billingAddress);
        }

        return $data;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return array
     */
    public function parseAddressData($address)
    {
        return array(
            'first_name' => $address->getFirstName(),
            'last_name' => $address->getLastname(),
            'address_line1' => $address->getStreetLine(1),
            'address_line2' => $address->getStreetLine(2),
            'city' => $address->getCity(),
            'state' => $address->getRegion(),
            'zip' => $address->getPostcode(),
            'phone_number' => $address->getTelephone()
        );
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function parseCartData($quote)
    {
        $data = array();
        $items = $quote->getAllVisibleItems();
        $index = 0;
        foreach ($items as $itemId => $item) {
            $data[$index] = array(
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'quantity' => $item->getQtyOrdered(),
                'price' => $item->getPrice()
            );
            $index++;
        }
        return $data;
    }

    public function getURL()
    {
        return $this->config->test_mode ? $this->config->test_url : $this->config->api_url;
    }

    public function getKey()
    {
        return $this->config->test_mode ? $this->config->test_key : $this->config->server_key;
    }

}
