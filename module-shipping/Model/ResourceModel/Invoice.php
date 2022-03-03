<?php

namespace Myparcelasia\Shipping\Model\ResourceModel;

class Invoice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('mpa_shipping_invoice', 'id');
    }
}