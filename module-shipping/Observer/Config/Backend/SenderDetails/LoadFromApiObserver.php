<?php

namespace Myparcelasia\Shipping\Observer\Config\Backend\SenderDetails;

use Myparcelasia\Shipping\Helper\Config;
use Myparcelasia\Shipping\Service\Mpa;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class LoadFromApiObserver implements ObserverInterface
{
    /**
     * Config helper
     *
     * @var \Myparcelasia\Shipping\Helper\Config
     */
    protected $configHelper;

    /**
     * LoadFromApiObserver constructor.
     *
     * @param \Myparcelasia\Shipping\Helper\Config $configHelper
     */
    public function __construct(
        Config $configHelper,
        Mpa $mpaService
    )
    {
        $this->configHelper = $configHelper;
        $this->mpaService = $mpaService;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Zend_Http_Client_Exception
     */
    public function execute(Observer $observer)
    {
        if ($this->configHelper->senderDetailsLoadFromApi()) {
            $this->gdexService->loadSenderDetailsFromApi();
        }
    }

}