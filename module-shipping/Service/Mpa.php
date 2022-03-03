<?php

namespace Myparcelasia\Shipping\Service;

use Myparcelasia\Shipping\Api\Mpa as MpaApi;
use Myparcelasia\Shipping\Helper\Config;
use Myparcelasia\Shipping\Model\Sales\Order;

class Mpa
{
    /**
     * Resouce config
     *
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * Cache type list
     *
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * Date time
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Country factory
     *
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * Myparcelasia api
     *
     * @var \Myparcelasia\Shipping\Api\Mpa
     */
    protected $mpaApi;

    /**
     * Config helper
     *
     * @var \Myparcelasia\Shipping\Helper\Config
     */
    protected $configHelper;

    /**
     * Myparcelasia constructor.
     *
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Myparcelasia\Shipping\Api\Mpa $mpaApi
     */
    public function __construct(
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        MpaApi $mpaApi,
        Config $configHelper
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->cacheTypeList  = $cacheTypeList;
        $this->dateTime       = $dateTime;
        $this->countryFactory = $countryFactory;
        $this->mpaApi         = $mpaApi;
        $this->configHelper   = $configHelper;
    }

    /**
     * Load sender details from api
     *
     * @throws \Zend_Http_Client_Exception
     */
    public function loadSenderDetailsFromApi()
    {
        $data = $this->mpaApi->getUserDetails();

        $this->resourceConfig->saveConfig('myparcelasia_shipping/sender_details/fullname', $data['fullname']);
        $this->resourceConfig->saveConfig('myparcelasia_shipping/sender_details/email', $data['email']);
        $this->resourceConfig->saveConfig('myparcelasia_shipping/sender_details/phone', $data['phone']);
        $this->resourceConfig->saveConfig('myparcelasia_shipping/sender_details/sender_address_line_1', $data['sender_address_line_1']);
        $this->resourceConfig->saveConfig('myparcelasia_shipping/sender_details/sender_address_line_2', $data['sender_address_line_2']);
        $this->resourceConfig->saveConfig('myparcelasia_shipping/sender_details/sender_postcode', $data['sender_postcode']);
        $this->resourceConfig->saveConfig('myparcelasia_shipping/sender_details/sender_city', $data['sender_city']);
        $this->resourceConfig->saveConfig('myparcelasia_shipping/sender_details/sender_state', $data['sender_state']);
        $this->resourceConfig->saveConfig('myparcelasia_shipping/sender_details/sender_country', $data['sender_country']);

        $this->cacheTypeList->cleanType('config');
        $this->cacheTypeList->cleanType('full_page');
    }

    
}