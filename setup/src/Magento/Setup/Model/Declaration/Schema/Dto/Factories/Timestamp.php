<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Factories;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * This format is used to save date (year, month, day).
 * Probably your SQL engine will save date in this format: 'YYYY-MM-DD HH:MM::SS'
 * Date time in invalid format will be converted to '0000-00-00 00:00:00' string
 * MySQL timestamp is similar to UNIX timestamp. You can pass you local time there and it will
 * be converted to UTC timezone. Then when you will try to pull your time back it will be converted
 * to your local time again.
 * Unix range: 1970-01-01 00:00:01' UTC to '2038-01-09 03:14:07'
 */
class Timestamp implements FactoryInterface
{
    /** Nullable timestamp */
    const NULL_TIMESTAMP = 'NULL';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $className;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param BooleanUtils           $booleanUtils
     * @param string                 $className
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        BooleanUtils $booleanUtils,
        $className = \Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp::class
    ) {
        $this->objectManager = $objectManager;
        $this->className = $className;
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * Change on_update and default params
     *
     * {@inheritdoc}
     *
     * @return array
     */
    public function create(array $data)
    {
        $data['onUpdate'] = isset($data['on_update']) ? $data['on_update'] : null;
        //As we have only one value for timestamp on update -> it is convinient to use boolean type for it
        //But later we need to convert it to SQL value
        if ($data['onUpdate'] && $data['onUpdate'] !== 'CURRENT_TIMESTAMP') {
            if ($this->booleanUtils->toBoolean($data['onUpdate'])) {
                $data['onUpdate'] = 'CURRENT_TIMESTAMP';
            } else {
                unset($data['onUpdate']);
            }
        }
        //By default we do not want to use default attribute
        if (!isset($data['default'])) {
            $data['default'] = self::NULL_TIMESTAMP;
        }

        return $this->objectManager->create($this->className, $data);
    }
}
