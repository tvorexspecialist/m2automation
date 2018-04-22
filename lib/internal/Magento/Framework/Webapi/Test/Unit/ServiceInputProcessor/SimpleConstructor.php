<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor;

class SimpleConstructor
{
    /**
     * @var int
     */
    private $entityId;

    /**
     * @var string
     */
    private $name;

    public function __construct(
        int $entityId,
        string $name
    ) {
        $this->entityId = $entityId;
        $this->name = $name;
    }

    /**
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
}
