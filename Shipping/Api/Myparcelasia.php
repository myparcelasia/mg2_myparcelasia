<?php

namespace Myparcelasia\Shipping\Api;

use Myparcelasia\Shipping\Helper\Config;
use Myparcelasia\Shipping\Logger\Api\Myparcelasia\Logger;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;

class Myparcelasia
{
    const ENDPOINT_GET_USER_TOKEN_VALIDITY = 'index';
    const ENDPOINT_GET_USER_DETAILS = 'user';
    const ENDPOINT_CHECK_WALLET_BALANCE = 'checkEWalletBalance';
    const ENDPOINT_GET_POSTCODE_LOCATIONS = 'GetPostcodeLocations';
    const ENDPOINT_GET_PICKUP_DATE_LISTING = 'GetPickupDateListing';
    const ENDPOINT_GET_SHIPPING_RATE = 'GetShippingRate';
    const ENDPOINT_CREATE_CONSIGNMENT = 'CreateConsignment';
    const ENDPOINT_GET_LAST_SHIPMENT_STATUS = 'GetLastShipmentStatus';
    const ENDPOINT_GET_CONSIGNMENTS_IMAGE = 'GetConsignmentsImage';
    const ENDPOINT_GET_CONSIGNMENTS_IMAGES_ZIP = 'GetConsignmentsImagesZip';
    const ENDPOINT_CANCEL_CONSIGNMENT = 'CancelConsignment';
    const ENDPOINT_GET_UPCOMING_PICKUP_DETAILS = 'GetUpcomingPickupDetails';
    const ENDPOINT_GET_PICKUP_REFERENCE = 'GetPickupReference';
    const ENDPOINT_CANCEL_PICKUP = 'CancelPickup';

    /**
     * Config
     *
     * @var \Myparcelasia\Shipping\Helper\Config
     */
    protected $config;


    /**
     * Zend client factory
     *
     * @var \Magento\Framework\HTTP\ZendClient
     */
    protected $zendClientFactory;

    /**
     * Logger
     *
     * @var \Myparcelasia\Shipping\Logger\Api\Myparcelasia\Logger
     */
    protected $logger;

    /**
     * Api constructor.
     *
     * @param \Myparcelasia\Shipping\Helper\Config $config
     * @param \Magento\Framework\HTTP\ZendClientFactory $zendClientFactory
     * @param \Myparcelasia\Shipping\Logger\Api\Myparcelasia\Logger $logger
     */
    public function __construct(
        Config $config,
        ZendClientFactory $zendClientFactory,
        Logger $logger
    ) {
        $this->config            = $config;
        $this->zendClientFactory = $zendClientFactory;
        $this->logger            = $logger;
    }

    /**
     * Get end point url
     *
     * @param $endpoint
     *
     * @return string
     */
    protected function endpoint(string $endpoint)
    {
        return $this->config->apiUrl() . $endpoint;
    }

    /**
     * Get headers
     *
     * @param string $token
     *
     * @return array
     */
    protected function headers(string $token = null)
    {
        $headers = [
            'Content-Type'     => 'application/json',
            'User-Token'       => $token ?: $this->config->apiUserAccessToken(),
        ];

        return $headers;
    }

    /**
     * Get user token validity
     *
     * @param string $token
     *
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function getUserTokenValidity(string $token = null)
    {
        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_GET_USER_TOKEN_VALIDITY));
        $client->setMethod(ZendClient::GET);

        return $this->request($client);
    }

    /**
     * Get user details
     *
     * @param string $token
     *
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function getUserDetails(string $token = null)
    {
        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_GET_USER_DETAILS));
        $client->setMethod(ZendClient::GET);

        return $this->request($client);
    }

    /**
     * Check e-wallet balance
     *
     * @param $token
     *
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function checkEWalletBalance($token = null)
    {
        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_CHECK_WALLET_BALANCE));
        $client->setMethod(ZendClient::GET);

        return $this->request($client);
    }

    /**
     * Get postcode locations
     *
     * @param $postcode
     * @param null $token
     *
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function getPostcodeLocations($postcode, $token = null)
    {
        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_GET_POSTCODE_LOCATIONS));
        $client->setMethod(ZendClient::GET);
        $client->setParameterGet('Postcode', $postcode);

        return $this->request($client);
    }

    /**
     * Get pickup date listing
     *
     * @param $postcode
     * @param null $token
     *
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function getPickupDateListing($postcode, $token = null)
    {
        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_GET_PICKUP_DATE_LISTING));
        $client->setMethod(ZendClient::GET);
        $client->setParameterGet('Postcode', $postcode);

        return $this->request($client);
    }

    /**
     * Get shipping ratees
     *
     * @param $shipments
     * @param null $token
     *
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function getShippingRates($shipments, $token = null)
    {
        $body = [];

        foreach ($shipments as $shipment) {
            $body[] = array_merge([
                'ReferenceNumber' => time(),
                'FromPostCode'    => '',
                'ToPostCode'      => '',
                'ParcelType'      => '',
                'Weight'          => '',
                'Country'         => '',
            ], $shipment);
        }

        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_GET_SHIPPING_RATE));
        $client->setMethod(ZendClient::POST);
        $client->setRawData(json_encode($body));

        return $this->request($client);
    }

    /**
     * Get shipping rate
     *
     * @param $shipment
     * @param null $token
     *
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function getShippingRate($shipment, $token = null)
    {
        return $this->getShippingRates([$shipment], $token)[0];
    }

    /**
     * Create consignment
     *
     * @param $payload
     * @param null $token
     *
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function createConsignment($payload, $token = null)
    {
        $payload = array_merge([
            'sender'       => [],
            'pickUp'       => [],
            'consignments' => [],
        ], $payload);

        $body = array_merge([
            'Name'     => '',
            'Mobile'   => '',
            'Email'    => '',
            'Address1' => '',
            'Address2' => '',
            'Address3' => '',
            'Postcode' => '',
            'City'     => '',
            'State'    => '',
        ], $payload['sender']);

        if ($payload['pickUp']) {
            $body['Pickup'] = array_merge([
                'Transportation'    => '',
                'ParcelReadyTime'   => '',
                'PickupDate'        => '',
                'PickupRemark'      => '',
                'IsTrolleyRequired' => false,
            ], $payload['pickUp']);
        }

        foreach ($payload['consignments'] as $consignment) {
            $body['Consignments'][] = array_merge([
                'OrderId'         => '',
                'ShipmentContent' => '',
                'ParcelType'      => '',
                'ShipmentValue'   => '',
                'Pieces'          => '',
                'Weight'          => '',
                'Name'            => '',
                'Mobile'          => '',
                'Email'           => '',
                'Address1'        => '',
                'Address2'        => '',
                'Address3'        => '',
                'Postcode'        => '',
                'City'            => '',
                'State'           => '',
                'Country'         => '',
            ], $consignment);
        }

        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_CREATE_CONSIGNMENT));
        $client->setMethod(ZendClient::POST);
        $client->setRawData(json_encode($body));

        return $this->request($client);
    }

    /**
     * Get last shipment status
     *
     * @param $shipments
     * @param null $token
     *
     * @return \Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function getLastShipmentStatus($shipments, $token = null)
    {
        if ( ! is_array($shipments)) {
            $shipments = [$shipments];
        }

        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_GET_LAST_SHIPMENT_STATUS));
        $client->setMethod(ZendClient::POST);
        $client->setRawData(json_encode($shipments));

        return $this->request($client);
    }

    public function getConsignmentsImage($consignmentNumber, $token = null)
    {
        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_GET_CONSIGNMENTS_IMAGE));
        $client->setMethod(ZendClient::GET);
        $client->setParameterGet('ConsignmentNumber', $consignmentNumber);

        return $this->request($client);
    }

    public function getConsignmentsImagesZip($consignmentNumbers, $token = null)
    {
        if ( ! is_array($consignmentNumbers)) {
            $consignmentNumbers = [$consignmentNumbers];
        }

        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_GET_CONSIGNMENTS_IMAGES_ZIP));
        $client->setMethod(ZendClient::POST);
        $client->setRawData(json_encode($consignmentNumbers));

        return $this->request($client);
    }

    public function getUpcomingPickupDetails($token = null)
    {
        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_GET_UPCOMING_PICKUP_DETAILS));
        $client->setMethod(ZendClient::GET);

        return $this->request($client);
    }

    public function cancelConsignment($consignmentNumber, $token = null)
    {
        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_CANCEL_CONSIGNMENT));
        $client->setMethod(ZendClient::PUT);
        $client->setParameterGet('ConsignmentNumber', $consignmentNumber);

        return $this->request($client);
    }

    public function getPickupReference($consignmentNo, $token = null)
    {
        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_GET_PICKUP_REFERENCE));
        $client->setMethod(ZendClient::GET);
        $client->setParameterGet('consignmentNo', $consignmentNo);

        return $this->request($client);
    }

    public function cancelPickup($pickupNumber, $token = null)
    {
        $client = $this->client($token);
        $client->setUri($this->endpoint(self::ENDPOINT_CANCEL_PICKUP));
        $client->setMethod(ZendClient::PUT);
        $client->setParameterGet('PickupNumber', $pickupNumber);

        return $this->request($client);
    }

    /**
     * @param $token
     *
     * @return \Magento\Framework\HTTP\ZendClient
     * @throws \Zend_Http_Client_Exception
     */
    protected function client($token = null)
    {
        $client = $this->zendClientFactory->create();
        $client->setConfig(['timeout' => 30]);
        $client->setHeaders($this->headers($token));

        return $client;
    }

    /**
     * Request
     *
     * @param \Magento\Framework\HTTP\ZendClient $client
     *
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    protected function request(ZendClient $client)
    {
        if ( ! $client->getHeader('User-Token')) {
            throw new \BadMethodCallException('Gdex user access token is not set.');
        }

        $response     = $client->request();
        $responseBody = json_decode($response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $responseBody = $response->getBody();
        }

        if ($response->isError()) {
            throw new \Zend_Http_Client_Exception($responseBody['message'] ?? $response->getMessage(), $response->getStatus());
        }

        $this->logger->info($client->getUri(true), [
            'request'  => $client->getLastRequest(),
            'response' => $response->getBody(),
        ]);

        return $responseBody['data'] ?? $responseBody;
    }
}