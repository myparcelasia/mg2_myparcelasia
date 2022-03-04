<?php

namespace Myparcelasia\Shipping\Block\Adminhtml\Shipment\Create;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var string
     */
    protected $_template = 'Myparcelasia_Shipping::shipment/create/form.phtml';

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $addressConfig;

    /**
     * @var \Myparcelasia\Shipping\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Myparcelasia\Shipping\Helper\Data
     */
    protected $dataHelper;

    /**
     * Myparcelasia service
     *
     * @var \Myparcelasia\Shipping\Service\Myparcelasia
     */
    protected $mpaService;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Myparcelasia\Shipping\Helper\Config $configHelper,
        \Myparcelasia\Shipping\Helper\Data $dataHelper,
        \Myparcelasia\Shipping\Service\Myparcelasia $mpaService,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->addressConfig = $addressConfig;
        $this->configHelper = $configHelper;
        $this->dataHelper = $dataHelper;
        $this->mpaService = $mpaService;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id'     => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post',
            ],
        ]);
        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSenderDetails($field = null)
    {
        return $this->configHelper->senderDetails($field);
    }

    public function getLocations($postalCode)
    {
        return $this->dataHelper->getLocations($postalCode);
    }

    public function getPickUpDates($postalCode)
    {
        return $this->dataHelper->getPickUpDate($postalCode);
    }

    public function getOrders()
    {
        return $this->_coreRegistry->registry('mpa_shipping_shipment_orders');
    }

    /**
     * GGet consignments
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConsignments()
    {
        $consignments = [];
        foreach ($this->getOrders() as $order) {
            /**
             * @var \Myparcelasia\Shipping\Model\Sales\Order $order
             */

            $order = $this->mpaService->quoteShippingEstimateByOrder($order);

            $consignments[] = [
                'orderId'          => $order->getId(),
                'orderIncrementId' => $order->getIncrementId(),
                'shippingAddress'  => $this->getAddressHtml($order->getShippingAddress()),
                'content'          => $order->createMpaShippingConsignmentContent(),
                'parcelType'       => $this->configHelper->consignmentParcelType(),
                'value'            => $order->getBaseGrandTotal(),
                'pieces'           => 1,
                'weight'           => $order->getWeight(),
                'name'             => $order->getShippingAddress()->getName(),
                'rate'             => $order->getMpaShippingEstimate(),
                'isLoadingRate'    => false,
            ];
        }

        return $consignments;
    }

    public function getWalletBalance()
    {
        return $this->dataHelper->getWalletBalance();
    }

    public function formatPickUpTime($time)
    {
        $time = new \DateTime($time, new \DateTimeZone($this->configHelper->timezone()));

        return $this->formatTime($time);
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param $address
     * @return string
     */
    protected function getAddressHtml($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($address);
    }
}