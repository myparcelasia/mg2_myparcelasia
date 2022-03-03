<?php

namespace Myparcelasia\Shipping\Block\Adminhtml\Sales\Order\View\Tab;

class Mpa extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'sales/order/view/tab/mpa.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Country factory
     *
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * Locale date
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Myparcelasia\Shipping\Helper\Data
     */
    protected $mpaDataHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Myparcelasia\Shipping\Helper\Data $mpaDataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->coreRegistry = $registry;
        $this->countryFactory = $countryFactory;
        $this->localeDate = $localeDate;
        $this->mpaDataHelper = $mpaDataHelper;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Myparcelasia\Shipping\Model\Sales\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    public function getEstimatePostcode()
    {
        return $this->getOrder()->getShippingAddress()->getPostcode();
    }

    public function getEstimateCountry()
    {
        return $this->countryFactory->create()->load($this->getOrder()->getShippingAddress()->getCountryId());
    }

    public function getEstimateWeight()
    {
        return $this->getOrder()->getWeight();
    }

    public function getEstimateRate()
    {
        return $this->getOrder()->getGdexShippingEstimate() ?: 0.00;
    }

    public function getEstimateQuotedAt()
    {
        return $this->formatDate(
            $this->localeDate->date(new \DateTime($this->getOrder()->getGdexShippingEstimateQuotedAt())),
            \IntlDateFormatter::MEDIUM,
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('MyParcel Asia');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('MyParcel Asia');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return $this->gdexDataHelper->isStoredApiUserAccessTokenValid();
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}