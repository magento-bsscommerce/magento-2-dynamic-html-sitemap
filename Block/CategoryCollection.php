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

use Magento\Directory\Helper\Data;

class CategoryCollection extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    public $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    public $categoryHelper;

    /**
     * @var $helper
     */
    public $helper;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    public $categoryFlatConfig;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    public $pageFactory;


    /**
     * ItemsCollection constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Bss\HtmlSiteMap\Helper\Data $helper
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Bss\HtmlSiteMap\Helper\Data $helper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Cms\Model\PageFactory $pageFactory,
        array $data = []
    ) {
        $this->pageFactory = $pageFactory;
        $this->categoryFlatConfig = $categoryFlatState;
        $this->helper = $helper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryHelper = $categoryHelper;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return \Bss\HtmlSiteMap\Helper\Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
    /**
     * @param bool $isActive
     * @param bool $level
     * @param bool $sortBy
     * @param bool $pageSize
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        
        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }
                
        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }
        
        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }
        
        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }
        
        return $collection;
    }

    /**
     * @return \Magento\Catalog\Helper\Category
     */
    public function getCategoryHelper()
    {
        return $this->categoryHelper;
    }

    /**
     * @return \Magento\Cms\Model\Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCmsPages()
    {
        $storeId = $this->getStoreId();
        $this->getStoreId();
        $collection = $this->pageFactory->create()->getCollection();
        $collection->addFieldToSelect("*");
        $collection->addStoreFilter($storeId);
        //$collection->load(1);
        return $collection;
    }

    /**
     * @param bool $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection
     */
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->categoryHelper->getStoreCategories($sorted, $asCollection, $toLoad);
    }

    /**
     * @param object $category
     * @return array
     */
    public function getChildCategories($category)
    {
        if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        return $subcategories;
    }

    /**
     * @param object $category
     * @param bool $categoryDisable
     * @return string
     */
    public function getAllCategories($category, $categoryDisable)
    {
        $categoryHelper = $this->getCategoryHelper();
        $categoryHtmlEnd = null;
        if ($childrenCategories = $this->getChildCategories($category)) {
            foreach ($childrenCategories as $category) {
                if (!$category->getIsActive()) {
                    continue;
                }
                $categoryString = (string)$category->getId();
                $categoryString = ",".$categoryString.",";
                $categoryValidate = strpos($categoryDisable, $categoryString);
                if ($categoryValidate == false) {
                    $categoryUrl = $categoryHelper->getCategoryUrl($category);
                    $categoryHtml = '<li><a href="'.$categoryUrl.'">'.$category->getName().'</a></li>';
                    $categoryReturn = $this->getAllCategories($category, $categoryDisable);
                    $categoryHtml = $categoryHtml.$categoryReturn;
                } else {
                    $categoryHtml = null;
                }
                $categoryHtmlEnd = $categoryHtmlEnd.$categoryHtml;
            }
            return '<ul>'.$categoryHtmlEnd.'</ul>';
        }
        return '';
    }
}
