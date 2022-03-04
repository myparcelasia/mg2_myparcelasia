<?php

namespace Myparcelasia\Shipping\Service;

use Myparcelasia\Shipping\Api\Myparcelasia as MpaApi;
use Myparcelasia\Shipping\Helper\Config;
use Myparcelasia\Shipping\Model\Sales\Order;

class Myparcelasia
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
     * @var \Myparcelasia\Shipping\Api\Myparcelasia
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
     * @param \Myparcelasia\Shipping\Api\Myparcelasia $mpaApi
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

        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/name', $data['Name']);
        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/email', $data['Email']);
        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/mobile_number', $data['MobileNumber']);
        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/address1', $data['Address1']);
        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/address2', $data['Address2']);
        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/postal_code', $data['PostalCode']);
        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/location_id', $data['LocationId']);
        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/location', $data['Location']);
        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/city', $data['City']);
        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/state', $data['State']);
        $this->resourceConfig->saveConfig('gdex_shipping/sender_details/country', $data['Country']);

        $this->cacheTypeList->cleanType('config');
        $this->cacheTypeList->cleanType('full_page');
    }

    /**
     * Quote shipping estimate
     *
     * @param $params
     *
     * @return mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function quoteShippingEstimate($params)
    {
        $shippingRateResult = $this->mpaApi->getShippingRate([
            'FromPostCode' => $params['FromPostCode'] ?? $this->configHelper->senderDetails('postal_code'),
            'ToPostCode'   => $params['ToPostCode'],
            'Country'      => $params['Country'],
            'Weight'       => $params['Weight'],
            'ParcelType'   => $params['ParcelType'] ?? $this->configHelper->consignmentParcelType(),
        ]);

        if ($shippingRateResult['HasError']) {
            throw new \Zend_Http_Client_Exception($shippingRateResult['Error']);
        }

        return $shippingRateResult['Rate'];
    }

    /**
     * Quote shipping estimate by order
     *
     * @param \Myparcelasia\Shipping\Model\Sales\Order $order
     * @param array $params
     *
     * @return \Myparcelasia\Shipping\Model\Sales\Order
     * @throws \Zend_Http_Client_Exception
     */
    public function quoteShippingEstimateByOrder(Order $order, $params = [])
    {
        $countryId = $order->getShippingAddress()->getCountryId();

        /**
         * @var \Magento\Directory\Model\Country $country
         */
        $country = $this->countryFactory->create()->load($countryId);

        $shippingRate = $this->quoteShippingEstimate([
            'FromPostCode' => $params['FromPostCode'] ?? null,
            'ToPostCode'   => $params['ToPostCode'] ?? $order->getShippingAddress()->getPostcode(),
            'Country'      => $params['Country'] ?? $country->getData('iso3_code'),
            'Weight'       => $params['Weight'] ?? $order->getWeight(),
            'ParcelType'   => $params['ParcelType'] ?? null,
        ]);

        $order->setMpaShippingEstimate($shippingRate);
        $order->setMpaShippingEstimateQuotedAt($this->dateTime->gmtDate());

        return $order;
    }
}