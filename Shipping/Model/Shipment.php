<?php

namespace Myparcelasia\Shipping\Model;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Shipment
 * @package Myparcelasia\Shipping\Model
 *
 * @method Shipment setSenderName()
 * @method string getSenderName()
 * @method Shipment setSenderMobileNumber(string $value)
 * @method string getSenderMobileNumber()
 * @method Shipment setSenderEmail(string $value)
 * @method string getSenderEmail()
 * @method Shipment setSenderAddress1(string $value)
 * @method Shipment setSenderAddress2(string $value)
 * @method Shipment setSenderPostalCode(string $value)
 * @method string getSenderPostalCode()
 * @method Shipment setSenderCity(string $value)
 * @method string getSenderCity()
 * @method Shipment setSenderState(string $value)
 * @method string getSenderState()
 */
class Shipment extends \Magento\Framework\Model\AbstractModel
{
    const SERVICE_TYPE_LODGE_IN = 'lodge in';
    const SERVICE_TYPE_PICK_UP = 'pick up';

    const PICK_UP_TRANSPORTATION_VAN = 'van';
    const PICK_UP_TRANSPORTATION_MOTORBIKE = 'motorbike';
    const PICK_UP_TRANSPORTATION_TRUCK = 'truck';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Locale date
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Myparcelasia\Shipping\Model\ResourceModel\Consignment\CollectionFactory
     */
    protected $consignmentCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Myparcelasia\Shipping\Model\ResourceModel\Consignment\CollectionFactory $consignmentCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->date = $date;
        $this->localeDate = $localeDate;
        $this->consignmentCollectionFactory = $consignmentCollectionFactory;
    }

    protected function _construct()
    {
        $this->_init(\Myparcelasia\Shipping\Model\ResourceModel\Shipment::class);
    }

    public function getConsignmentsCollection()
    {
        $collection = $this->consignmentCollectionFactory->create()->setShipmentFilter($this);

        if ($this->getId()) {
            foreach ($collection as $consignment) {
                $consignment->setShipment($this);
            }
        }

        return $collection;
    }

    public function getSenderAddress1()
    {
        return $this->getData('sender_address1');
    }

    public function getSenderAddress2()
    {
        return $this->getData('sender_address2');
    }

    /**
     * @return \Myparcelasia\Shipping\Model\Consignment[]
     */
    public function getConsignments()
    {
        if ($this->getData('consignments') === null) {
            $this->setConsignments($this->getConsignmentsCollection()->getItems());
        }

        return $this->getData('consignments');
    }

    /**
     * Get consignment by order it
     *
     * @param $orderId
     *
     * @return \Myparcelasia\Shipping\Model\Consignment
     */
    public function getConsignmentsByOrderId($orderId)
    {
        foreach ($this->getConsignments() as $consignment) {
            if ($consignment->getOrderId() == $orderId) {
                return $consignment;
            }
        }
    }

    /**
     * Set consignments
     *
     * @param $consignments
     *
     * @return \Myparcelasia\Shipping\Model\Shipment
     */
    public function setConsignments($consignments)
    {
        return $this->setData('consignments', $consignments);
    }

    /**
     *
     *
     * @param \Myparcelasia\Shipping\Model\Consignment $consignment
     *
     * @return $this
     */
    public function addConsignment(\Myparcelasia\Shipping\Model\Consignment $consignment)
    {
        $consignment->setShipment($this);
        if (!$consignment->getId()) {
            $this->setConsignments(array_merge($this->getConsignments(), [$consignment]));
        }

        return $this;
    }

    public function beforeSave()
    {
        parent::beforeSave();

        if ( ! $this->getPickUpReadyAt() && $this->getPickUpDate() && $this->getPickUpTime()) {
            $pickUpReadyAt = str_replace('00:00:00', $this->getPickUpTime(), $this->getPickUpDate());
            $pickUpReadyAt = $this->date->gmtDate(null, $pickUpReadyAt);

            $this->setPickUpReadyAt($pickUpReadyAt);
        }

        return $this;
    }
}