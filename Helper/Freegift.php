<?php
/**
 * Pharmacare, Inc.
 *
 * @category    IMGateway
 * @package     ConfigurableProduct
 * @Author      D N N Udugala
 * @email       nuwinda.udugala@pharmacare.com.au
 */
namespace Theiconnz\Freegift\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Constructor
 *
 * @param Context $context
 * @param StoreManagerInterface $storeManager
 */
class Freegift extends AbstractHelper
{
    const CONFIG_MODULE_PATH = 'freegift';
    const XML_PATH_ENABLED = 'enable';
    const XML_PATH_ITEM = 'item';
    const XML_PATH_NOOFITEMS = 'noofitemincart';
    const XML_PATH_MESSAGE = 'message';

    const XML_PATH_CODE = 'code';

    const OPTION_PREFIX = '_prefix';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;


    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
    }

    public function isEnabled($storeId = null): ?string
    {
        return $this->getConfigGeneral(static::XML_PATH_ENABLED, $storeId);
    }
    public function getItem($storeId = null): ?string
    {
        return $this->getConfigGeneral(static::XML_PATH_ITEM, $storeId);
    }
    public function getNoofItem($storeId = null): ?string
    {
        return $this->getConfigGeneral(static::XML_PATH_NOOFITEMS, $storeId);
    }
    public function getPrefix($storeId = null): ?string
    {
        return static::OPTION_PREFIX;
    }

    public function getCode($storeId = null): ?string
    {
        return $this->getConfigGeneral(static::XML_PATH_CODE, $storeId);
    }
    public function getMessage($storeId = null): ?string
    {
        return $this->getConfigGeneral(static::XML_PATH_MESSAGE, $storeId);
    }

    /**
     * @param string $code
     * @param mixed $storeId
     * @return string | null
     */
    public function getConfigGeneral(string $code = '', mixed $storeId = 0) : string | null
    {
        $code = ($code !== '') ? '/' . $code : '';
        return $this->getConfigValue(static::CONFIG_MODULE_PATH . '/general' . $code, $storeId);
    }

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null): mixed
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
