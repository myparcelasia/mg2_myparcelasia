<?php

namespace Myparcelasia\Shipping\Model\Config\Source\Consignment;

class ParcelType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Myparcelasia\Shipping\Model\Consignment::PARCEL_TYPE_PARCEL,
                'label' => __('Parcel')
            ],
            [
                'value' => \Myparcelasia\Shipping\Model\Consignment::PARCEL_TYPE_DOCUMENT,
                'label' => __('Document')
            ],
        ];
    }
}