<?php

namespace Myparcelasia\Shipping\Model;

/**
 * Class Invoice
 * @package Myparcelasia\Shipping\Model
 *
 * @method Invoice setShipmentId(string $value)
 * @method int getShipmentId()
 */
class Invoice extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Myparcelasia\Shipping\Model\Shipment
     */
    protected $shipment;

    /**
     * Invoice constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Myparcelasia\Shipping\Model\ShipmentRepository $shipmentRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Myparcelasia\Shipping\Model\ShipmentRepository $shipmentRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->shipmentRepository = $shipmentRepository;
    }

    protected function _construct()
    {
        $this->_init(\Myparcelasia\Shipping\Model\ResourceModel\Invoice::class);
    }

    /**
     * @param \Myparcelasia\Shipping\Model\Shipment $shipment
     *
     * @return $this
     */
    public function setShipment(\Myparcelasia\Shipping\Model\Shipment $shipment)
    {
        $this->shipment = $shipment;

        $this->setShipmentId($this->shipment->getId());

        return $this;
    }

    /**
     * @return \Myparcelasia\Shipping\Model\Shipment
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getShipment()
    {
        if ( ! $this->shipment && $this->getShipmentId()) {
            $this->shipment = $this->shipmentRepository->get($this->getShipmentId());
        }

        return $this->shipment;
    }

    /**
     * Before save
     *
     * @return $this|\Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if ( ! $this->getShipmentId() && $this->getShipment()) {
            $this->setShipmentId($this->getShipment()->getId());
        }

        return $this;
    }
}