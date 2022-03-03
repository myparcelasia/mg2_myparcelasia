<?php

namespace Myparcelasia\Shipping\Controller\Adminhtml\Shipment;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Order collection factory
     *
     * @var \Magento\Reports\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * MyParcel Asia service
     *
     * @var \Myparcelasia\Shipping\Service\Mpa
     */
    protected $mpaService;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Myparcelasia\Shipping\Service\Mpa $mpaService
    ) {
        parent::__construct($context);

        $this->resultPageFactory      = $resultPageFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->coreRegistry           = $coreRegistry;
        $this->mpaService            = $mpaService;
    }

    public function execute()
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addFieldToFilter('entity_id', ['in' => $this->getRequest()->getParams('orders')])->load();
        $orderCollection->load();

        if ( ! $orderCollection->count()) {
            $this->messageManager->addErrorMessage(__('There are no selected orders for creating new MyParcel Asia shipment.'));

            $redirect = $this->resultRedirectFactory->create();
            $redirect->setPath('sales/order');

            return $redirect;
        }

        $this->coreRegistry->register('mpa_shipping_shipment_orders', $orderCollection->getItems());

        $resultPage = $this->resultPageFactory->create();

        $resultPage->addBreadcrumb(__('Myparcel Asia Shipping '), __('Myparcel Asia Shipping'))
                   ->addBreadcrumb(__('Manage Shipments'), __('Manage Shipments'))
                   ->addBreadcrumb(__('New Shipments'), __('New Shipments'));

        $resultPage->getConfig()->getTitle()->prepend(__('Shipments'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Shipment'));

        return $resultPage;
    }
}
