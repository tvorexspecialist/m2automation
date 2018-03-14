<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestModuleGraphQlQuery\Model\Resolver;

use Magento\Framework\GraphQl\Query\PostFetchProcessorInterface;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\TestModuleGraphQlQuery\Api\Data\ItemInterface;
use Magento\TestModuleGraphQlQuery\Model\Entity\ItemFactory;
use Magento\Framework\GraphQl\Config\Data\Field;
use GraphQL\Type\Definition\ResolveInfo;

class Item implements ResolverInterface
{
    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var PostFetchProcessorInterface[]
     */
    private $postFetchProcessors;

    /**
     * @param ItemFactory $itemFactory
     * @param PostFetchProcessorInterface[] $postFetchProcessors
     */
    public function __construct(ItemFactory $itemFactory, array $postFetchProcessors = [])
    {
        $this->itemFactory = $itemFactory;
        $this->postFetchProcessors = $postFetchProcessors;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ) : ?array {
        $id = 0;
        foreach ($args as $key => $argValue) {
            if ($key === "id") {
                $id = (int)$argValue;
            }
        }

        /** @var ItemInterface $item */
        $item = $this->itemFactory->create();
        $item->setItemId($id);
        $item->setName("itemName");
        $itemData = [
            'item_id' => $item->getItemId(),
            'name' => $item->getName()
        ];

        foreach ($this->postFetchProcessors as $postFetchProcessor) {
            $itemData = $postFetchProcessor->process($itemData);
        }

        return $itemData;
    }
}
