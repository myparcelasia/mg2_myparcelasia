<?php

namespace Myparcelasia\Shipping\Model\ResourceModel\Invoice;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Myparcelasia\Shipping\Model\Invoice::class, \Myparcelasia\Shipping\Model\ResourceModel\Invoice::class);
    }
}