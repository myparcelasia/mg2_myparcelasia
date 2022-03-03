<?php

namespace Myparcelasia\Shipping\Observer\Sales\Frontend\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class QuoteShippingEstimateObserver implements ObserverInterface
{
    /**
     * @var \Myparcelasia\Shipping\Helper\Data
     */
    protected $mpaDataHelper;

    /**
     * MyParcel Asia service
     *
     * @var \Myparcelasia\Shipping\Service\Mpa
     */
    protected $mpaService;

    /**
     * QuoteShippingEstimateObserver constructor.
     *
     * @param \Myparcelasia\Shipping\Service\Mpa $mpaService
     */
    public function __construct(
        \Myparcelasia\Shipping\Helper\Data $mpaDataHelper,
        \Myparcelasia\Shipping\Service\Mpa $mpaService
    )
    {
        $this-$mpaDataHelper = $mpaDataHelper;
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
       if ( ! $this->mpaDataHelper->isStoredApiUserAccessTokenValid()) {
           return;
       }

        /* @var \Myparcelasia\Shipping\Model\Sales\Order $order */
        $order = $observer->getEvent()->getOrder();

        $this->mpaService->quoteShippingEstimateByOrder($order);
    }
}