<?php

 namespace Myparcelasia\Shipping\Model\ResourceModel\Consignment;

 class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
 {
     /**
      * Shipment object
      *
      * @var \Myparcelasia\Shipping\Model\Shipment
      */
     protected $shipment = null;

     /**
      * Order field for setOrderFilter
      *
      * @var string
      */
     protected $shipmentField = 'shipment_id';

     /**
      *
      */
     protected function _construct()
     {
         $this->_init(\Myparcelasia\Shipping\Model\Consignment::class, \Myparcelasia\Shipping\Model\ResourceModel\Consignment::class);
     }

     /**
      * @param \Myparcelasia\Shipping\Model\Shipment $shipment
      *
      * @return $this
      */
     public function setShipment(\Myparcelasia\Shipping\Model\Shipment $shipment)
     {
         $this->shipment = $shipment;

         return $this;
     }

     /**
      * Add shipment filter
      *
      * @param $shipment
      *
      * @return $this
      */
     public function setShipmentFilter($shipment)
     {
         if ($shipment instanceof \Myparcelasia\Shipping\Model\Shipment) {
             $this->setShipment($shipment);
             if ($shipmentId = $shipment->getId()) {
                 $this->addFieldToFilter('shipment_id', $shipmentId);
             } else {
                 $this->_totalRecords = 0;
                 $this->_setIsLoaded(true);
             }
         } else {
             $this->addFieldToFilter('shipment_id', $shipment);
         }

         return $this;
     }
 }