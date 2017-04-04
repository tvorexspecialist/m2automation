<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CmsUrlRewrite\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;

class CmsPageUrlRewriteGenerator
{
    /**
     * Entity type code
     */
    const ENTITY_TYPE = 'cms-page';

    /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory */
    protected $urlRewriteFactory;

    /** @var \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator */
    protected $cmsPageUrlPathGenerator;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $cmsPage;

    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator $cmsPageUrlPathGenerator
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        CmsPageUrlPathGenerator $cmsPageUrlPathGenerator,
        StoreManagerInterface $storeManager
    ) {
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->storeManager = $storeManager;
        $this->cmsPageUrlPathGenerator = $cmsPageUrlPathGenerator;
    }

    /**
     * @param \Magento\Cms\Model\Page $cmsPage
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function generate($cmsPage)
    {
        $stores = $cmsPage->getStores();
        $this->cmsPage = $cmsPage;
        $urls = array_search('0', $stores) === false ? $this->generateForSpecificStores($stores)
            : $this->generateForAllStores();
        $this->cmsPage = null;
        return $urls;
    }

    /**
     * Generate list of urls for default store
     *
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForAllStores()
    {
        $urls = [];
        foreach ($this->storeManager->getStores() as $store) {
            $urls[] = $this->createUrlRewrite($store->getStoreId());
        }
        return $urls;
    }

    /**
     * Generate list of urls per store
     *
     * @param int[] $storeIds
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForSpecificStores($storeIds)
    {
        $urls = [];
        $existingStores = $this->storeManager->getStores();
        foreach ($storeIds as $storeId) {
            if (!isset($existingStores[$storeId])) {
                continue;
            }
            $urls[] = $this->createUrlRewrite($storeId);
        }
        return $urls;
    }

    /**
     * Create url rewrite object
     *
     * @param int $storeId
     * @param int $redirectType
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite
     */
    protected function createUrlRewrite($storeId, $redirectType = 0)
    {
        return $this->urlRewriteFactory->create()->setStoreId($storeId)
            ->setEntityType(self::ENTITY_TYPE)
            ->setEntityId($this->cmsPage->getId())
            ->setRequestPath($this->cmsPage->getIdentifier())
            ->setTargetPath($this->cmsPageUrlPathGenerator->getCanonicalUrlPath($this->cmsPage))
            ->setIsAutogenerated(1)
            ->setRedirectType($redirectType);
    }
}
