<?php

namespace Myparcelasia\Shipping\Model;

class PickUpRepository
{
    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Locale date
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Myparcelasia\Shipping\Api\Mpa
     */
    protected $mpaApi;

    /**
     * @var \Myparcelasia\Shipping\Model\ShipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var array
     */
    protected $pickUpReferences = [];

    /**
     * PickUpRepository constructor.
     *
     * @param \Myparcelasia\Shipping\Api\Mpa $mpaApi
     */
    public function __construct(
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Myparcelasia\Shipping\Api\Mpa $mpaApi,
        \Myparcelasia\Shipping\Helper\Config $configHelper,
        \Myparcelasia\Shipping\Model\ShipmentRepository $shipmentRepository
    ) {
        $this->collectionFactory  = $collectionFactory;
        $this->localeDate         = $localeDate;
        $this->mpaApi             = $mpaApi;
        $this->configHelper       = $configHelper;
        $this->shipmentRepository = $shipmentRepository;
    }

    public function queryReference(Consignment $consignment)
    {
        $shipment = $consignment->getShipment();

        if ($shipment->getServiceType() !== \Myparcelasia\Shipping\Model\Shipment::SERVICE_TYPE_PICK_UP) {
            return;
        }

        $consignmentNumber = $consignment->getNumber();

        if (empty($this->pickUpReferences[$consignmentNumber])) {
            try {
                $pickupResponse = $this->mpaApi->getPickupReference($consignmentNumber);
            } catch (\Zend_Http_Client_Exception $exception) {
                if ($exception->getMessage() !== 'Pickup Is Already Cancelled') {
                    throw $exception;
                }

                $shipment->setServiceType(\Myparcelasia\Shipping\Model\Shipment::SERVICE_TYPE_LODGE_IN);

                $this->shipmentRepository->save($shipment);

                return;
            }

            $this->pickUpReferences[$consignmentNumber] = [
                'number'           => $pickupResponse['PickupNo'],
                'ready_at'         => new \DateTime($consignment->getShipment()->getPickUpReadyAt(), new \DateTimeZone($this->configHelper->timezone())),
                'transportation'   => $consignment->getShipment()->getPickUpTransportation(),
                'trolley_required' => $consignment->getShipment()->getPickUpTrolleyRequired(),
                'remark'           => $consignment->getShipment()->getRemark(),
                'status'           => $pickupResponse['Status'],
            ];
        }

        return $this->pickUpReferences[$consignmentNumber];
    }

    /**
     * @return \Magento\Framework\Data\Collection
     * @throws \Exception
     */
    public function queryUpcoming()
    {
        $pickUps = [];
        foreach ($this->mpaApi->getUpcomingPickupDetails() as $pickupDetail) {
            $pickUpReadyAt = new \DateTime(
                str_replace('T00:00:00', ' ' . $pickupDetail['ParcelReadyTime'], $pickupDetail['PickupTime']),
                new \DateTimeZone($this->configHelper->timezone())
            );

            $pickUps[] = [
                'id'                  => $pickupDetail['PickupNo'],
                'address'             => $pickupDetail['PickupAddress'],
                'ready_at'            => $pickUpReadyAt->setTimezone(new \DateTimeZone('utc'))->format('Y-m-d h:i:s'),
                'remark'              => $pickupDetail['PickupRemark'],
                'transportation'      => $pickupDetail['Transportation'],
                'is_trolley_required' => $pickupDetail['IsTrolleyRequired'],
                'status'              => $pickupDetail['Status'],
                'is_cancellable'      => $pickupDetail['Status'] === 'Pending',
            ];
        }

        usort($pickUps, function ($pickup1, $pickup2) {
            $pickup1ReadyAt = $pickup1['ready_at'];
            $pickup2ReadyAt = $pickup2['ready_at'];

            if ($pickup1ReadyAt < $pickup2ReadyAt) {
                return -1;
            }

            if ($pickup1ReadyAt > $pickup2ReadyAt) {
                return 1;
            }

            return 0;
        });

        $pickUpCollection = $this->collectionFactory->create();

        foreach ($pickUps as $pickUp) {
            $pickUpCollection->addItem(new \Magento\Framework\DataObject($pickUp));
        }

        return $pickUpCollection;
    }

    public function cancel($pickUpNumber)
    {
        $this->mpaApi->cancelPickup($pickUpNumber);

        return true;
    }
}