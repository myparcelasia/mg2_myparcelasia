<?php

namespace Myparcelasia\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{
    const XML_PATH_TIMEZONE = 'myparcelasia_shipping/timezone';
    const XML_PATH_API_USER_ACCESS_TOKEN = 'myparcelasia_shipping/api/user_access_token';
    const XML_PATH_PRODUCTION_API_URL = 'myparcelasia_shipping/production_api/url';
    const XML_PATH_PRODUCTION_API_SUBSCRIPTION_KEY = 'myparcelasia_shipping/production_api/subscription_key';
    const XML_PATH_TESTING_API_URL = 'myparcelasia_shipping/testing_api/url';
    const XML_PATH_TESTING_API_SUBSCRIPTION_KEY = 'myparcelasia_shipping/testing_api/subscription_key';
    const XML_PATH_TESTING = 'myparcelasia_shipping/testing';
    const XML_PATH_SENDER_DETAILS_LOAD_FROM_API = 'myparcelasia_shipping/sender_details/load_from_api';
    const XML_PATH_SENDER_DETAILS = 'myparcelasia_shipping/sender_details';
    const XML_PATH_CONSIGNMENT_PARCEL_TYPE = 'myparcelasia_shipping/consignment/parcel_type';

    /**
     * Get timezone
     *
     * @return string
     */
    public function timezone()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TIMEZONE);
    }

    /**
     * Get base url
     *
     * @return string
     */
    public function apiUrl()
    {
        return $this->isTestingMode() ? $this->testingApiUrl() : $this->productionApiUrl();
    }

    /**
     * Get api user acess token
     *
     * @return string
     */
    public function apiUserAccessToken()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_USER_ACCESS_TOKEN);
    }

    /**
     * Get production api url
     *
     * @return string
     */
    public function productionApiUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCTION_API_URL);
    }

    /**
     * Get testing api url
     *
     * @return string
     */
    public function testingApiUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TESTING_API_URL);
    }

    /**
     * Get api subscription key
     *
     * @return mixed
     */
    public function apiSubscriptionKey()
    {
        return $this->isTestingMode() ? $this->testingApiSubscriptionKey() : $this->productionApiSubscriptionKey();
    }

    /**
     * Get production api subscription key
     *
     * @return mixed
     */
    public function productionApiSubscriptionKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCTION_API_SUBSCRIPTION_KEY);
    }

    /**
     * Get testing api subscription key
     *
     * @return mixed
     */
    public function testingApiSubscriptionKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TESTING_API_SUBSCRIPTION_KEY);
    }

    /**
     * Check should load sender details from api
     *
     * @return bool
     */
    public function senderDetailsLoadFromApi()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SENDER_DETAILS_LOAD_FROM_API);
    }

    /**
     * Get sender details
     *
     * @param null $field
     *
     * @return mixed
     */
    public function senderDetails($field = null)
    {
        $senderDetails = array_merge([
            'name'          => '',
            'email'         => '',
            'mobile_number' => '',
            'address1'      => '',
            'address2'      => '',
            'postal_code'   => '',
            'location_id'   => '',
            'location'      => '',
            'city'          => '',
            'state'         => '',
            'country'       => '',
        ], $this->scopeConfig->getValue(self::XML_PATH_SENDER_DETAILS), []);

        if ($field) {
            return $senderDetails[$field];
        }

        return $senderDetails;
    }

    /**
     * Get consignment default parcel type
     *
     * @return string
     */
    public function consignmentParcelType()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CONSIGNMENT_PARCEL_TYPE);
    }

    /**
     * Check is testing mode
     *
     * @return bool
     */
    public function isTestingMode()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_TESTING);
    }
}