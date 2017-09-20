<?php
namespace Kash\Gateway\Model;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Offsite extends Adapter
{
    protected $_code  = \Kash\Gateway\Model\Config::METHOD_GATEWAY_KASH;
    protected $_formBlockType = 'kash_gateway/form_bb';
    protected $_infoBlockType = 'kash_gateway/adminhtml_info';

    protected $_canUseInternal              = false;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;

    /**
     * Config instance
     *
     * @var \Kash\Gateway\Model\Config
     */
    protected $_config = null;

    /**
     * Config model type
     *
     * @var string
     */
    protected $_configType = 'kash_gateway/config';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Kash\Gateway\Helper\GatewayHelper
     */
    protected $gatewayHelper;

    /**
     * @param ManagerInterface          $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory  $paymentDataObjectFactory
     * @param string                    $code
     * @param string                    $formBlockType
     * @param string                    $infoBlockType
     * @param CommandPoolInterface      $commandPool
     * @param ValidatorPoolInterface    $validatorPool
     * @param CommandManagerInterface   $commandExecutor
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        StoreManagerInterface $storeManager,
        \Kash\Gateway\Helper\GatewayHelper $gatewayHelper,
        \Kash\Gateway\Model\Config $config,
        \Magento\Framework\UrlInterface $urlInterface = null,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null
    ) {
        $this->storeManager = $storeManager;
        $this->gatewayHelper = $gatewayHelper;
        $this->_config = $config;
        $this->urlInterface = $urlInterface;
        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor
        );
    }

    /**
     * Config instance setter
     *
     * @param  \Kash\Gateway\Model\Config $instace
     * @param  int                        $storeId
     * @return $this
     */
    public function setConfig(\Kash\Gateway\Model\Config $instace, $storeId = null)
    {
        $this->_config = $instace;
        if (null !== $storeId) {
            $this->_config->setStoreId($storeId);
        }
        return $this;
    }

    /**
     * Config instance getter
     *
     * @return \Kash\Gateway\Model\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }


    /**
     * Store setter
     * Also updates store ID in config object
     *
     * @param  \Magento\Store\Model\Store|int $store
     * @return \Kash\Gateway\Model\Offsite
     */
    public function setStore($store)
    {
        if (null === $store) {
            $store = $this->storeManager->getStore()->getId();
        }
        $this->_config->setStoreId(is_object($store) ? $store->getId() : $store);
        return $this;
    }

    /**
     * Custom getter for payment configuration
     *
     * @param  string $field
     * @param  int    $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->_config->$field;
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see    Mage_Checkout_OnepageController::savePaymentAction()
     * @see    Mage_Sales_Model_Quote_Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->urlInterface->getUrl('kash/offsite/start', array('_secure'=>true));
    }

    /**
     * Whether can get recurring profile details
     */
    public function canGetRecurringProfileDetails()
    {
        return true;
    }

}
