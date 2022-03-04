<?php

namespace Myparcelasia\Shipping\Controller\Adminhtml\Consignment;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Myparcelasia\Shipping\Helper\Data
     */
    protected $dataHelper;

    /**
     * Index constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Myparcelasia\Shipping\Helper\Config $config
     * @param \Myparcelasia\Shipping\Helper\Data $data
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Myparcelasia\Shipping\Helper\Data $data
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->dataHelper        = $data;
    }

    public function execute()
    {
        if ( ! $this->dataHelper->isStoredApiUserAccessTokenValid()) {
            $this->messageManager->addErrorMessage(__('A valid user token is required to complete MyPrcel Asia Shipping setup.'));

            $redirect = $this->resultRedirectFactory->create();
            $redirect->setPath('adminhtml/system_config/edit/section/myparcelasia_shipping');

            return $redirect;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('MyPrcel Asia Shipping'), __('MyPrcel Asia Shipping'));
        $resultPage->addBreadcrumb(__('Consignments'), __('Consignments'));
        $resultPage->getConfig()->getTitle()->prepend((__('Consignments')));

        return $resultPage;
    }
}