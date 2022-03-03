<?php

namespace Myparcelasia\Shipping\Model;

/**
 * Class Consignment
 * @package Myparcelasia\Shipping\Model
 *
 * @method Consignment setOrderId(string $value)
 * @method int getOrderId()
 * @method Consignment setShipmentId(string $value)
 * @method int getShipmentId()
 * @method string getNumber()
 * @method string getStatus()
 */
class Consignment extends \Magento\Framework\Model\AbstractModel
{
    const PARCEL_TYPE_PARCEL = 'parcel';
    const PARCEL_TYPE_DOCUMENT = 'document';

    const STATUS_PENDING = 'Pending';
    const STATUS_DELIVERED = 'Delivered';
    const STATUS_RETURNED = 'Returned';
    const STATUS_CLAIMED = 'Claimed';
    const STATUS_CANCELLED = 'Cancelled';
    const STATUS_EXPIRED = 'Expired';

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @var \Myparcelasia\Shipping\Model\ShipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var \Myparcelasia\Shipping\Model\ConsignmentRepository
     */
    protected $consignmentRepository;

    /**
     * @var \Myparcelasia\Shipping\Model\PickUpRepository
     */
    protected $pickUpRepository;

    /**
     * @var \Myparcelasia\Shipping\Model\Shipment
     */
    protected $shipment;

    /**
     * Consignment constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Myparcelasia\Shipping\Model\ShipmentRepository $shipmentRepository
     * @param \Myparcelasia\Shipping\Model\ConsignmentRepository $consignmentRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Myparcelasia\Shipping\Model\ShipmentRepository $shipmentRepository,
        \Myparcelasia\Shipping\Model\ConsignmentRepository $consignmentRepository,
        \Myparcelasia\Shipping\Model\PickUpRepository $pickUpRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->orderRepository = $orderRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->consignmentRepository = $consignmentRepository;
        $this->pickUpRepository = $pickUpRepository;
    }

    protected function _construct()
    {
        $this->_init(\Myparcelasia\Shipping\Model\ResourceModel\Consignment::class);
    }

    public function getAddress1()
    {
        return $this->getData('address1');
    }

    public function getAddress2()
    {
        return $this->getData('address2');
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return $this
     */
    public function setOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $this->order = $order;

        $this->setOrderId($this->order->getId());

        return $this;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrder()
    {
        if ( ! $this->order && $this->getOrderId()) {
            $this->order = $this->orderRepository->get($this->getOrderId());
        }

        return $this->order;
    }

    public function setShipment(\Gdex\Shipping\Model\Shipment $shipment)
    {
        $this->shipment = $shipment;

        $this->setShipmentId($this->shipment->getId());

        return $this;
    }

    public function getShipment()
    {
        if ( ! $this->shipment && $this->getShipmentId()) {
            $this->shipment = $this->shipmentRepository->get($this->getShipmentId());
        }

        return $this->shipment;
    }

    public function getPickUpReference()
    {
        return $this->pickUpRepository->queryReference($this);
    }

    public function getPickUpNumber()
    {
        $pickUpReference = $this->getPickUpReference();
        if (!empty($pickUpReference['number'])) {
            return $pickUpReference['number'];
        }
    }

    public function getPickUpReadyAt()
    {
        $pickUpReference = $this->getPickUpReference();
        if (!empty($pickUpReference['ready_at'])) {
            return $pickUpReference['ready_at'];
        }
    }

    public function getPickUpTransportation()
    {
        $pickUpReference = $this->getPickUpReference();
        if (!empty($pickUpReference['transportation'])) {
            return $pickUpReference['transportation'];
        }
    }

    public function getPickUpTrolleyRequired()
    {
        $pickUpReference = $this->getPickUpReference();
        if (!empty($pickUpReference['trolley_required'])) {
            return $pickUpReference['trolley_required'];
        }
    }

    public function getPickUpRemark()
    {
        $pickUpReference = $this->getPickUpReference();
        if (!empty($pickUpReference['remark'])) {
            return $pickUpReference['remark'];
        }
    }

    public function getPickUpStatus()
    {
        $pickUpReference = $this->getPickUpReference();
        if (!empty($pickUpReference['status'])) {
            return $pickUpReference['status'];
        }
    }

    /**
     * Get latest status
     *
     * @return string
     * @throws \Zend_Http_Client_Exception
     */
    public function getLatestStatus()
    {
        return $this->consignmentRepository->getLatestStatus($this);
    }

    public function getImage()
    {
        return $this->consignmentRepository->queryImage($this);
    }

    public function isCancellable()
    {
        return $this->getLatestStatus() === self::STATUS_PENDING;
    }

    public function isPrintable()
    {
        return $this->getLatestStatus() === self::STATUS_PENDING;
    }

    /**
     * Before save
     *
     * @return $this|\Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if ( ! $this->getShipmentId() && $this->getShipment()) {
            $this->setShipmentId($this->getShipment()->getId());
        }

        if ( ! $this->getOrderId() && $this->getOrder()) {
            $this->setOrderId($this->getOrder()->getId());
        }

        return $this;
    }
}