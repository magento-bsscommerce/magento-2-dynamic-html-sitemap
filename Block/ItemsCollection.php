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

/**
 * Class ItemsCollection
 * @package Bss\HtmlSiteMap\Block
 */
class ItemsCollection extends \Magento\Framework\View\Element\Template
{
    const MAX_PRODUCTS = 'bss_htmlsitemap/product/max_products';
    const SORT_PRODUCT = 'bss_htmlsitemap/product/sort_product';
    const ORDER_PRODUCT = 'bss_htmlsitemap/product/order_product';
    const PRODUCT_LIST_NUMBER = '1';
    const STORE_VIEW_LIST_NUMBER = '2';
    const ADDITIONAL_LIST_NUMBER = '3';
    const CATE_AND_CMS_NUMBER = '4';
    const XML_PATH_DEFAULT_LOCALE = 'general/locale/code';

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    public $categoryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Store\Block\Switcher\Interceptor
     */
    public $interceptor;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var $helper
     */
    public $helper;
    
    /**
     * @var bool
     */
    public $storeInUrl;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    public $postDataHelper;

    /**
     * ItemsCollection constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Bss\HtmlSiteMap\Helper\Data $helper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Bss\HtmlSiteMap\Helper\Data $helper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();
        $this->postDataHelper = $postDataHelper;
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
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * @param bool $fromStore
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreUrl($fromStore = true)
    {
        return $this->storeManager->getStore()->getCurrentUrl($fromStore);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isStoreActive()
    {
        return $this->storeManager->getStore()->isActive();
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentGroupId()
    {
        return $this->_storeManager->getStore()->getGroupId();
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRawGroups()
    {
        if (!$this->hasData('raw_groups')) {
            $websiteGroups = $this->_storeManager->getWebsite()->getGroups();

            $groups = [];
            foreach ($websiteGroups as $group) {
                $groups[$group->getId()] = $group;
            }
            $this->setData('raw_groups', $groups);
        }
        return $this->getData('raw_groups');
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRawStores()
    {
        if (!$this->hasData('raw_stores')) {
            $websiteStores = $this->_storeManager->getWebsite()->getStores();
            $stores = [];
            foreach ($websiteStores as $store) {
                if (!$store->isActive()) {
                    continue;
                }
                $localeCode = $this->_scopeConfig->getValue(
                    self::XML_PATH_DEFAULT_LOCALE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                );
                $store->setLocaleCode($localeCode);
                $params = ['_query' => []];
                if (!$this->isStoreInUrl()) {
                    $params['_query']['___store'] = $store->getCode();
                }
                $baseUrl = $store->getUrl('', $params);

                $store->setHomeUrl($baseUrl);
                $stores[$store->getGroupId()][$store->getId()] = $store;
            }
            $this->setData('raw_stores', $stores);
        }
        return $this->getData('raw_stores');
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGroups()
    {
        if (!$this->hasData('groups')) {
            $rawGroups = $this->getRawGroups();
            $rawStores = $this->getRawStores();

            $groups = [];
            $localeCode = $this->_scopeConfig->getValue(
                self::XML_PATH_DEFAULT_LOCALE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            foreach ($rawGroups as $group) {
                if (!isset($rawStores[$group->getId()])) {
                    continue;
                }
                if ($group->getId() == $this->getCurrentGroupId()) {
                    $groups[] = $group;
                    continue;
                }

                $store = $group->getDefaultStoreByLocale($localeCode);

                if ($store) {
                    $group->setHomeUrl($store->getHomeUrl());
                    $groups[] = $group;
                }
            }
            $this->setData('groups', $groups);
        }
        return $this->getData('groups');
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStores()
    {
        if (!$this->getData('stores')) {
            $rawStores = $this->getRawStores();

            $groupId = $this->getCurrentGroupId();
            if (!isset($rawStores[$groupId])) {
                $stores = [];
            } else {
                $stores = $rawStores[$groupId];
            }
            $this->setData('stores', $stores);
        }
        return $this->getData('stores');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isStoreInUrl()
    {
        if ($this->storeInUrl === null) {
            $this->storeInUrl = $this->_storeManager->getStore()->isUseStoreInUrl();
        }
        return $this->storeInUrl;
    }

    /**
     * Returns target store post data
     *
     * @param \Magento\Store\Model\Store $store
     * @param array $data
     * @return string
     */
    public function getTargetStorePostData(\Magento\Store\Model\Store $store, $data = [])
    {
        $data[\Magento\Store\Api\StoreResolverInterface::PARAM_NAME] = $store->getCode();
        return $this->postDataHelper->getPostData(
            $this->getUrl('/'),
            $data
        );
    }
}
