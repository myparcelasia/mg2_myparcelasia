<?php

namespace Myparcelasia\Shipping\Model\Config\Backend\Api;

use Myparcelasia\Shipping\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

class Token extends \Magento\Framework\App\Config\Value
{
    /**
     * Data helper
     *
     * @var \Myparcelasia\Shipping\Helper\Data
     */
    protected $dataHelper;

    /**
     * Token constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Myparcelasia\Shipping\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $this->dataHelper = $dataHelper;
    }

    /**
     * Before save
     *
     * @return \Magento\Framework\App\Config\Value|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    public function beforeSave()
    {
        $isTokenValid = $this->dataHelper->isApiUserAccessTokenValid($this->getValue());
        if ( ! $isTokenValid) {
            throw new LocalizedException(__('Invalid user access token.'));
        }
    }
}
