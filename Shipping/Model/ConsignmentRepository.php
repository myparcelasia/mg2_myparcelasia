<?php

namespace Myparcelasia\Shipping\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class ConsignmentRepository
{
    /**
     * @var \Myparcelasia\Shipping\Model\Consignment[]
     */
    protected $instances = [];

    /**
     * @var array
     */
    protected $statuses = [];

    /**
     * @var \Myparcelasia\Shipping\Model\ConsignmentFactory
     */
    protected $consignmentFactory;

    /**
     * @var \Myparcelasia\Shipping\Model\ResourceModel\Consignment
     */
    protected $consignmentResourceModel;

    /**
     * @var \Myparcelasia\Shipping\Api\Myparcelasia
     */
    protected $mpaApi;

    /**
     * ConsignmentRepository constructor.
     *
     * @param  \Myparcelasia\Shipping\Model\ConsignmentFactory  $consignmentFactory
     * @param  \Myparcelasia\Shipping\Model\ResourceModel\Consignment  $consignmentResourceModel
     */
    public function __construct(
        \Myparcelasia\Shipping\Model\ConsignmentFactory $consignmentFactory,
        \Myparcelasia\Shipping\Model\ResourceModel\Consignment $consignmentResourceModel,
        \Myparcelasia\Shipping\Api\Myparcelasia $mpaApi
    ) {
        $this->consignmentFactory       = $consignmentFactory;
        $this->consignmentResourceModel = $consignmentResourceModel;
        $this->mpaApi                   = $mpaApi;
    }

    /**
     * @param $id
     *
     * @return \Myparcelasia\Shipping\Model\Consignment
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        if (empty($this->instances[$id])) {
            /**
             * @var \Myparcelasia\Shipping\Model\Consignment $consignment
             */
            $this->consignmentResourceModel->load($consignment = $this->consignmentFactory->create(), $id);

            if ( ! $consignment->getId()) {
                throw new NoSuchEntityException(__('Requested consignment doesn\'t exist'));
            }

            $this->instances[$id] = $consignment;
        }

        return $this->instances[$id];
    }

    /**
     * Get latest statuses
     *
     * @param  \Myparcelasia\Shipping\Model\Consignment[]  $consignments
     *
     * @return string[]
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Http_Client_Exception
     */
    public function getLatestStatuses(array $consignments)
    {
        $askedStatuses           = [];
        $nonCachedConsignments   = [];
        $toBeQueriedConsignments = [];

        foreach ($consignments as $consignment) {
            if ( ! ($consignment instanceof Consignment)) {
                $consignment = $this->get($consignment);
            }
            if (empty($this->statuses[$consignment->getId()])) {
                $nonCachedConsignments[] = $consignment;
            } else {
                $askedStatuses[$consignment->getId()] = $this->statuses[$consignment->getId()];
            }
        }

        foreach ($nonCachedConsignments as $consignment) {
            if ($consignment->getStatus()) {
                $this->statuses[$consignment->getId()] = $askedStatuses[$consignment->getId()] = $consignment->getStatus();
            } else {
                $toBeQueriedConsignments[] = $consignment;
            }
        }

        if ($toBeQueriedConsignments) {
            $queriedConsignmentStatuses = $this->queryStatuses(array_map(function (Consignment $consignment) {
                return $consignment->getNumber();
            }, $toBeQueriedConsignments));

            foreach ($toBeQueriedConsignments as $consignment) {
                $latestConsignmentStatus = $queriedConsignmentStatuses[$consignment->getNumber()] ?? null;
                if (in_array($latestConsignmentStatus, [
                    Consignment::STATUS_DELIVERED,
                    Consignment::STATUS_RETURNED,
                    Consignment::STATUS_CLAIMED,
                    Consignment::STATUS_CANCELLED,
                ])) {
                    $this->save($consignment->setStatus($latestConsignmentStatus));
                }

                $this->statuses[$consignment->getId()] = $askedStatuses[$consignment->getId()] = $latestConsignmentStatus;
            }
        }

        return $askedStatuses;
    }

    /**
     * @param  \Myparcelasia\Shipping\Model\Consignment|int  $consignment
     *
     * @return string
     */
    public function getLatestStatus($consignment)
    {
        if ( ! ($consignment instanceof Consignment)) {
            $consignment = $this->get($consignment);
        }

        return $this->getLatestStatuses([$consignment])[$consignment->getId()];
    }

    /**
     * @param $consignmentNumbers
     *
     * @return array
     * @throws \Zend_Http_Client_Exception
     */
    public function queryStatuses($consignmentNumbers)
    {
        if ( ! is_array($consignmentNumbers)) {
            $consignmentNumbers = [$consignmentNumbers];
        }

        $getStatusesResult = $this->gdexApi->getLastShipmentStatus($consignmentNumbers);

        $statuses = [];
        foreach ($getStatusesResult as $getStatusResult) {
            $statuses[$getStatusResult['ConsignmentNote']] = $getStatusResult['ConsignmentNoteStatus'];
        }

        return $statuses;
    }

    /**
     * @param  \Myparcelasia\Shipping\Model\Consignment|string  $consignmentNumber
     *
     * @return array
     */
    public function queryImage($consignmentNumber)
    {
        if ($consignmentNumber instanceof Consignment) {
            $consignmentNumber = $consignmentNumber->getNumber();
        }

        $image = $this->mpaApi->getConsignmentsImage($consignmentNumber);

        return $image;
    }

    /**
     * Query image zip
     *
     * @param $consignmentNumbers
     *
     * @return bool|string
     */
    public function queryImageZip($consignmentNumbers)
    {
        if ( ! is_array($consignmentNumbers)) {
            $consignmentNumbers = [$consignmentNumbers];
        }

        $zip = $this->mpaApi->getConsignmentsImagesZip($consignmentNumbers);

        return base64_decode($zip);
    }

    /**
     * Save
     *
     * @param  \Myparcelasia\Shipping\Model\Consignment  $consignment
     *
     * @return \Myparcelasia\Shipping\Model\Consignment
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(Consignment $consignment)
    {
        try {
            $this->consignmentResourceModel->save($consignment);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save the consignment: %1', $exception->getMessage()),
                $exception
            );
        }

        return $consignment;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function cancel($id)
    {
        $consignment = $this->get($id);

        if ( ! $consignment->isCancellable()) {
            throw new \InvalidArgumentException('Consignment cannot be cancelled.');
        }

        $this->mpaApi->cancelConsignment($consignment->getNumber());

        return true;
    }
}