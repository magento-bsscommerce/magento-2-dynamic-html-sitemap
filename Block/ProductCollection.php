<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    BSS_HtmlSiteMap
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HtmlSiteMap\Block;

/**
 * Class ProductCollection
 * @package Bss\HtmlSiteMap\Block
 */
class ProductCollection extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $productCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var $helper
     */
    public $helper;

    /**
     * ItemsCollection constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Bss\HtmlSiteMap\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Bss\HtmlSiteMap\Helper\Data $helper,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->helper = $helper;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Bss\HtmlSiteMap\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        $maxProducts = $this->helper->getMaxProduct();
        $maxProducts = (int)$maxProducts;
        if ($maxProducts >= 0 && $maxProducts != null) {
            if ($maxProducts > 50000) {
                $maxProducts = 50000;
            }
        } else {
            $maxProducts = 50000;
        }

        $sortProduct = $this->helper->getSortProduct();
        $orderProduct = $this->helper->getOrderProduct();

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $rulerStatus = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $collection->addAttributeToFilter('status', $rulerStatus);
        $collection->addWebsiteFilter();
        $collection->addFieldToFilter([['attribute'=>'visibility', 'neq'=>"1" ]]);
        $collection->addUrlRewrite();
        $collection->addAttributeToSort($sortProduct, $orderProduct);
        $collection->setPageSize($maxProducts);
        return $collection;
    }
}
