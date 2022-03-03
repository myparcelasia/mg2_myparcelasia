<?php

namespace Myparcelasia\Shipping\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Myparcelasia\Shipping\Model\ShipmentRepository
     */
    protected $shipmentRepository;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Myparcelasia\Shipping\Model\ShipmentRepository $shipmentRepository
     */
    public function __construct(
        Action\Context $context,
        \Myparcelasia\Shipping\Model\ShipmentRepository $shipmentRepository
    ) {
        parent::__construct($context);

        $this->shipmentRepository = $shipmentRepository;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();

        $this->shipmentRepository->create($data['shipment'], $data['consignments']);

        $this->messageManager->addSuccessMessage(__('The MyParcel Asia shipment has been saved.'));

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('myparcelasia_shipping/consignment');

        return $redirect;
    }
}