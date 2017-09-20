<?php
namespace Kash\Gateway\Model;


/**
 * Config model that is aware of all Kash_Gateway methods
 * Works with Payment BB system configuration
 *
 * @author Blue Badger <jonathan@badger.blue>
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    /**
     * Config path for enabling/disabling order review step in express checkout
     */
    const XML_PATH_GATEWAY_KASH_SKIP_ORDER_REVIEW_STEP_FLAG = 'payment/kash_gateway/skip_order_review_step';

    /**
     * Website Payments Pro - BB Checkout
     *
     * @var string
     */
    const METHOD_GATEWAY_KASH = 'kash_gateway';

    /**
     * URL for get request
     *
     * @var string
     */
    const REQUEST_GATEWAY_KASH = 'kash/offsite/getRequest';

    /**
     *  Transaction type
     */
    const TRANSACTION_TYPE = 'x_transaction_type';

    /**
     * Current payment method code
     *
     * @var string
     */
    protected $_methodCode = null;

    /**
     * Current store id
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

     /**
      * @param ScopeConfigInterface $scopeConfig
      * @param string|null          $methodCode
      * @param string               $pathPattern
      */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
    
        $this->scopeConfig = $scopeConfig;
        $this->_methodCode = $methodCode;
        parent::__construct(
            $scopeConfig,
            $methodCode,
            $pathPattern
        );
    }

    /**
     * Method code setter +/
     *
     * @param  string $methodCode
     * @return void
     */
    public function setMethod($methodCode)
    {
        $this->_methodCode = $methodCode;
    }

    /**
     * Payment method instance code getter +/
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    /**
     * Store ID setter
     *
     * @param  int $storeId
     * @return \Kash\Gateway\Model\Config
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }


    /**
     * Config field magic getter +/
     * The specified key can be either in camelCase or under_score format
     * Tries to map specified value according to set payment method code, into the configuration value
     * Sets the values into public class parameters, to avoid redundant calls of this method
     *
     * @param  string $key
     * @return string|null
     */
    public function __get($key)
    {
        $underscored = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $key));
        $value = $this->scopeConfig->getValue($this->_mapMethodFieldset($underscored), \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeId);
        $this->$key = $value;
        $this->$underscored = $value;
        return $value;
    }

    /**
     * Map Payment BB General Settings
     * +/
     *
     * @param  string $fieldName
     * @return string|null
     */
    protected function _mapMethodFieldset($fieldName)
    {
        if (!$this->_methodCode) {
            return null;
        }
        return "payment/{$this->_methodCode}/{$fieldName}";
    }

    /**
     * Check whether order review step enabled in configuration
     *
     * @return bool
     */
    public function isOrderReviewStepDisabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GATEWAY_KASH_SKIP_ORDER_REVIEW_STEP_FLAG);
    }
}

