<?php

namespace Myparcelasia\Shipping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * Cache
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

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
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Myparcelasia\Shipping\Api\Mpa $mpaApi
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\CacheInterface $cache,
        \Myparcelasia\Shipping\Api\Mpa $mpaApi,
        \Myparcelasia\Shipping\Helper\Config $configHelper
    ) {
        parent::__construct($context);

        $this->cache        = $cache;
        $this->mpaApi      = $mpaApi;
        $this->configHelper = $configHelper;
    }

    public function isApiUserAccessTokenValid(string $token = null)
    {
        try {
            $this->mpaApi->getUserTokenValidity($token);

            return true;
        } catch (\Zend_Http_Client_Exception $exception) {
            return false;
        }
    }

    public function isStoredApiUserAccessTokenValid()
    {
        $token = $this->configHelper->apiUserAccessToken();

        if (!$token) {
            return false;
        }

        return $this->isApiUserAccessTokenValid($token);
    }

    public function getLocations($postalCode)
    {
        $locationCacheId = "gdex_shipping_locations_{$postalCode}";

        $locations = $this->cache->load($locationCacheId);
        if ($locations) {
            return json_decode($locations, true);
        }

        $place = $this->mpaApi->getPostcodeLocations($postalCode);

        $locations = [];
        foreach ($place['DistrictList'] as $district) {
            foreach ($district['LocationList'] as $location) {
                $locations[] = [
                    'id'    => $location['LocationId'],
                    'name'  => $location['Location'],
                    'city'  => $district['District'],
                    'state' => $place['State'],
                ];
            }
        }

        $this->cache->save(json_encode($locations), $locationCacheId, ['collection'], 86400);

        return $locations;
    }

    public function getPickUpDate($postalCode)
    {
        $dateCacheId = "gdex_shipping_pick_up_dates_{$postalCode}";

        $dates = $this->cache->load($dateCacheId);
        if ($dates) {
            return json_decode($dates, true);
        }

        $dateListing = $this->mpaApi->getPickupDateListing($postalCode);

        $dates = [];
        foreach ($dateListing as $date) {
            $date = new \DateTime($date, new \DateTimeZone($this->configHelper->timezone()));

            $dates[] = [
                'day'   => $date->format('l'),
                'date'  => $date->format('Y-m-d'),
                'value' => $date->format('Y-m-d\T00:00:00'),
            ];
        }

        $this->cache->save(json_encode($dates), $dateCacheId, [ 'collection' ], 86400);

        return $dates;
    }

    /**
     * Get wallet balance
     *
     * @return float
     * @throws \Zend_Http_Client_Exception
     */
    public function getWalletBalance()
    {
        try {
            return (float)$this->mpaApi->checkEWalletBalance();
        } catch (\Exception $exception) {
        }
    }
}