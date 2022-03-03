<?php

namespace Myparcelasia\Shipping\Model\Sales;

class Order extends \Magento\Sales\Model\Order
{
    /**
     * @var \Myparcelasia\Shipping\Model\Consignment
     */
    protected $mpaShippingConsignment;

    /**
     * Get MyParcel Asia shipping estimate
     *
     * @return mixed
     */
    public function getMpaShippingEstimate() {
        return $this->getData('mpa_shipping_estimate');
    }

    /**
     * Get Myparcel Asia shipping estimate quoted at
     *
     * @return mixed
     */
    public function getMpaShippingEstimateQuotedAt() {
        return $this->getData('mpa_shipping_estimate_quoted_at');
    }

    /**
     * Get Myparcel Asia shipping consignment content
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createMpaShippingConsignmentContent()
    {
        $categories = [];

        foreach ($this->getItems() as $item) {

            /**
             * @var \Magento\Sales\Model\Order\Item $item
             */

            $product = $item->getProduct();

            if ($product->isComposite()) {
                continue;
            }

            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

            /**
             * @var \Magento\Catalog\Model\CategoryRepository $categoryRepository
             */
            $categoryRepository = $objectManager->get(\Magento\Catalog\Model\CategoryRepository::class);

            foreach ($product->getCategoryIds() as $categoryId) {
                $category = $categoryRepository->get($categoryId);

                $categories[] = $category->getName();
            }
        }

        return implode(', ', array_unique($categories));
    }

    public function setMpaShippingConsignment(\Myparcelasia\Shipping\Model\Consignment $consignment)
    {
        $this->mpaShippingConsignment = $consignment;

        $this->setMpaShippingConsignmentId($consignment->getId());
        $this->setMpaShippingConsignmentNumber($consignment->getNumber());

        return $this;
    }
}