<?php

namespace Myparcelasia\Shipping\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\OrderRepository;

class ShipmentRepository
{
    /**
     * @var \Myparcelasia\Shipping\Model\Shipment[]
     */
    protected $instances = [];

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * Country factory
     *
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Myparcelasia\Shipping\Model\ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var \Myparcelasia\Shipping\Model\ResourceModel\Shipment
     */
    protected $shipmentResourceModel;

    /**
     * @var \Myparcelasia\Shipping\Model\ConsignmentFactory
     */
    protected $consignmentFactory;

    /**
     * @var \Myparcelasia\Shipping\Model\InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var \Myparcelasia\Shipping\Api\Mpa
     */
    protected $mpaApi;

    /**
     * ShipmentRepository constructor.
     *
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Myparcelasia\Shipping\Model\ShipmentFactory $shipmentFactory
     * @param \Myparcelasia\Shipping\Model\ResourceModel\Shipment $shipmentResourceModel
     * @param \Myparcelasia\Shipping\Model\ConsignmentFactory $consignmentFactory
     */
    public function __construct(
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Myparcelasia\Shipping\Model\ShipmentFactory $shipmentFactory,
        \Myparcelasia\Shipping\Model\ResourceModel\Shipment $shipmentResourceModel,
        \Myparcelasia\Shipping\Model\ConsignmentFactory $consignmentFactory,
        \Myparcelasia\Shipping\Model\InvoiceFactory $invoiceFactory,
        \Myparcelasia\Shipping\Api\Mpa $mpaApi
    ) {
        $this->transactionFactory    = $transactionFactory;
        $this->countryFactory        = $countryFactory;
        $this->orderRepository       = $orderRepository;
        $this->shipmentFactory       = $shipmentFactory;
        $this->shipmentResourceModel = $shipmentResourceModel;
        $this->consignmentFactory    = $consignmentFactory;
        $this->invoiceFactory        = $invoiceFactory;
        $this->mpaApi                = $mpaApi;
    }

    /**
     * Get
     *
     * @param $id
     *
     * @return \Myparcelasia\Shipping\Model\Shipment
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        if (empty($this->instances[$id])) {
            /**
             * @var \Myparcelasia\Shipping\Model\Shipment $shipment
             */
            $this->shipmentResourceModel->load($shipment = $this->shipmentFactory->create(), $id);
            if ( ! $shipment->getId()) {
                throw new NoSuchEntityException(__('Requested shipment doesn\'t exist'));
            }

            $this->instances[$id] = $shipment;
        }

        return $this->instances[$id];
    }

    /**
     * Create
     *
     * @param $shipmentInput
     * @param $consignmentInputs
     *
     * @return \Myparcelasia\Shipping\Model\Shipment
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function create($shipmentInput, $consignmentInputs)
    {
        $transaction = $this->transactionFactory->create();

        $shipment = $this->shipmentFactory->create()->setData($shipmentInput);
        $transaction->addObject($shipment);

        $createConsignmentPayload['sender'] = [
            'Name'       => $shipment->getSenderName(),
            'Mobile'     => $shipment->getSenderMobileNumber(),
            'Email'      => $shipment->getSenderEmail(),
            'Address1'   => $shipment->getSenderAddress1(),
            'Address2'   => $shipment->getSenderAddress2(),
            'Postcode'   => $shipment->getSenderPostalCode(),
            'LocationId' => $shipment->getSenderLocationId(),
            'Location'   => $shipment->getSenderLocation(),
            'City'       => $shipment->getSenderCity(),
            'State'      => $shipment->getSenderState(),
        ];


        if ($shipment->getServiceType() === \Myparcelasia\Shipping\Model\Shipment::SERVICE_TYPE_PICK_UP) {
            $createConsignmentPayload['pickUp'] = [
                'Transportation'    => $shipment->getPickUpTransportation(),
                'PickupDate'        => $shipment->getPickUpDate(),
                'ParcelReadyTime'   => $shipment->getPickUpTime(),
                'PickupRemark'      => $shipment->getPickUpRemark(),
                'IsTrolleyRequired' => (boolean)$shipment->getPickUpTrolleyRequired(),
            ];
        }

        foreach ($consignmentInputs as $consignmentInput) {
            /**
             * @var \Myparcelasia\Shipping\Model\Sales\Order $order
             */
            $order = $this->orderRepository->get($consignmentInput['order_id']);

            $shippingAddress = $order->getShippingAddress();

            $country = $this->countryFactory->create()->load($shippingAddress->getCountryId());

            /**
             * @var \Myparcelasia\Shipping\Model\Consignment $consignment
             */
            $consignment = $this->consignmentFactory->create();
            $consignment->addData([
                'content'  => $order->createMpaShippingConsignmentContent(),
                'value'    => $order->getBaseGrandTotal(),
                'name'     => $shippingAddress->getName(),
                'mobile'   => $shippingAddress->getTelephone(),
                'email'    => $shippingAddress->getEmail(),
                'address1' => $shippingAddress->getStreetLine(1),
                'address2' => $shippingAddress->getStreetLine(2),
                'postcode' => $shippingAddress->getPostcode(),
                'city'     => $shippingAddress->getCity(),
                'state'    => $shippingAddress->getRegion(),
                'country'  => $country->getData('iso3_code'),
            ]);
            $consignment->addData($consignmentInput);
            $consignment->setOrder($order);
            $transaction->addObject($consignment);

            $shipment->addConsignment($consignment);

            $createConsignmentPayload['consignments'][] = [
                'OrderId'         => $order->getId(),
                'ShipmentContent' => $consignment->getContent(),
                'ParcelType'      => $consignment->getParcelType(),
                'ShipmentValue'   => $consignment->getValue(),
                'Pieces'          => $consignment->getPieces(),
                'Weight'          => $consignment->getWeight(),
                'Name'            => $consignment->getName(),
                'Mobile'          => $consignment->getMobile(),
                'Email'           => $consignment->getEmail(),
                'Address1'        => $consignment->getAddress1(),
                'Address2'        => $consignment->getAddress2(),
                'Postcode'        => $consignment->getPostcode(),
                'City'            => $consignment->getCity(),
                'State'           => $consignment->getState(),
                'Country'         => $consignment->getCountry(),
            ];
        }

        $createConsignmentsResult = $this->mpaApi->createConsignment($createConsignmentPayload);

        foreach ($createConsignmentsResult['Consignments'] as $consignmentResult) {
            /**
             * @var \Myparcelasia\Shipping\Model\Consignment $consignment
             */
            $consignment = $shipment->getConsignmentsByOrderId($consignmentResult['OrderId']);
            $consignment->setNumber($consignmentResult['ConsignmentNumber']);
            $consignment->setRate($consignmentResult['Rate']);

            $comment = __('MyParcel Asia Consignment %1 is created, shipping rate is RM%2', $consignment->getNumber(), $consignment->getRate());

            $order = $consignment->getOrder();
            $order->setMpaShippingConsignment($consignment);
            $order->addCommentToStatusHistory($comment);
            $transaction->addObject($order);
        }

        $invoice = $this->invoiceFactory->create();
        $invoice->setShipment($shipment);
        $invoice->addData([
            'number' => $createConsignmentsResult['InvoiceNumber'],
            'total'  => $createConsignmentsResult['GrandTotal'],
        ]);
        $transaction->addObject($invoice);

        $transaction->save();

        return $shipment;
    }

    /**
     * Save
     *
     * @param \Myparcelasia\Shipping\Model\Shipment $shipment
     *
     * @return \Myparcelasia\Shipping\Model\Shipment
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(Shipment $shipment)
    {
        try {
            $this->shipmentResourceModel->save($shipment);
        }
        catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save the shipment: %1', $exception->getMessage()),
                $exception
            );
        }

        return $shipment;
    }
}