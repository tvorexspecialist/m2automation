<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Model\Link;

use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class SaveHandler
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var LinkRepository
     */
    protected $linkRepository;

    /**
     * @param LinkRepository $linkRepository
     */
    public function __construct(LinkRepository $linkRepository)
    {
        $this->linkRepository = $linkRepository;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity, $arguments = [])
    {
        if ($entity->getTypeId() !== 'downloadable') {
            return $entity;
        }
        $links = $entity->getExtensionAttributes()->getDownloadableProductLinks() ?: [];
        $updatedLinks = [];
        $oldLinks = $this->linkRepository->getList($entity->getSku());
        foreach ($links as $link) {
            if ($link->getId()) {
                $updatedLinks[$link->getId()] = true;
            }
            $this->linkRepository->save($entity->getSku(), $link, !(bool)$entity->getStoreId());
        }
        /** @var \Magento\Catalog\Api\Data\ProductInterface $entity */
        foreach ($oldLinks as $link) {
            if (!isset($updatedLinks[$link->getId()])) {
                $this->linkRepository->delete($link->getId());
            }
        }
        return $entity;
    }
}
