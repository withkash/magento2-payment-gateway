<?php
/**
 * Base payment information block
 *
 */
namespace Kash\Gateway\Block\Adminhtml;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * Payment rendered specific information
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_paymentSpecificInformation = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('kash/info/default.phtml');
    }

    public function getAdditionalInformation(){
        return $this->getInfo()->getAdditionalInformation();
    }
}
