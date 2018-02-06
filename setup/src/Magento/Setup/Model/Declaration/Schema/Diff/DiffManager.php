<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Diff;

use Magento\Setup\Model\Declaration\Schema\Comparator;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\Operations\AddColumn;
use Magento\Setup\Model\Declaration\Schema\Operations\AddComplexElement;
use Magento\Setup\Model\Declaration\Schema\Operations\CreateTable;
use Magento\Setup\Model\Declaration\Schema\Operations\DropElement;
use Magento\Setup\Model\Declaration\Schema\Operations\DropReference;
use Magento\Setup\Model\Declaration\Schema\Operations\DropTable;
use Magento\Setup\Model\Declaration\Schema\Operations\ModifyColumn;
use Magento\Setup\Model\Declaration\Schema\Operations\ModifyTable;
use Magento\Setup\Model\Declaration\Schema\Operations\ReCreateTable;

/**
 * Helper which provide methods, that helps to compare 2 different nodes:
 * For instance, 2 columns between each other.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiffManager
{
    /**
     * @var Comparator
     */
    private $comparator;

    /**
     * Constructor.
     *
     * @param Comparator $comparator
     */
    public function __construct(Comparator $comparator)
    {
        $this->comparator = $comparator;
    }

    /**
     * Check whether this is element is new or not, by checking it in db schema.
     *
     * @param  ElementInterface[] $generatedElements
     * @param  ElementInterface   $element
     * @return bool
     */
    public function shouldBeCreated(array $generatedElements, ElementInterface $element)
    {
        return !isset($generatedElements[$element->getName()]);
    }

    /**
     * Check whether we have elements that should be removed from database.
     *
     * @param  array $generatedElements
     * @return bool
     */
    public function shouldBeRemoved(array $generatedElements)
    {
        return !empty($generatedElements);
    }

    /**
     * Register element, that should changes.
     *
     * @param  Diff    $diff
     * @param  ElementInterface $element
     * @param  ElementInterface $generatedElement
     * @return DiffInterface
     */
    public function registerModification(
        Diff $diff,
        ElementInterface $element,
        ElementInterface $generatedElement
    ) {
        if ($element instanceof Column) {
            $diff->register($element, ModifyColumn::OPERATION_NAME, $generatedElement);
        } else {
            $diff = $this->registerRemoval($diff, [$generatedElement]);
            $diff = $this->registerCreation($diff, $element);
        }

        return $diff;
    }

    /**
     * If elements really dont exists in declaration - we will remove them.
     * If some mistake happens (and element is just not preprocessed), we will throw exception.
     *
     * @param  Diff      $diff
     * @param  ElementInterface[] $generatedElements
     * @return DiffInterface
     */
    public function registerRemoval(
        Diff $diff,
        array $generatedElements
    ) {
        foreach ($generatedElements as $generatedElement) {
            if ($generatedElement instanceof Reference) {
                $this->registerReferenceDrop($generatedElement, $diff);
                continue;
            }

            $operation = $generatedElement instanceof Table ? DropTable::OPERATION_NAME : DropElement::OPERATION_NAME;
            $diff->register($generatedElement, $operation, $generatedElement);
        }

        return $diff;
    }

    /**
     * Register creation.
     *
     * @param DiffInterface    $diff
     * @param ElementInterface $element
     * @return DiffInterface
     */
    public function registerCreation(DiffInterface $diff, ElementInterface $element)
    {
        if ($element instanceof Table) {
            $operation = CreateTable::OPERATION_NAME;
        } elseif ($element instanceof Column) {
            $operation = AddColumn::OPERATION_NAME;
        } else {
            $operation = AddComplexElement::OPERATION_NAME;
        }

        $diff->register($element, $operation);

        return $diff;
    }

    /**
     * We need to register drop of foreign key in scope of reference table.
     *
     * This done because reference table is goes first and starting from this table
     * there should be no foreign key on modified column.
     *
     * @param Reference $reference
     * @param Diff $diff
     * @return Diff
     */
    public function registerReferenceDrop(Reference $reference, Diff $diff)
    {
        $diff->register(
            $reference,
            DropReference::OPERATION_NAME,
            $reference,
            $reference->getReferenceTable()->getName()
        );
        return $diff;
    }

    /**
     * Depends on what should be changed we can re-create table or modify it.
     *
     * For example, we can modify table if we need to change comment or engine.
     * Or we can re-create table, when we need to change it shard.
     *
     * @param Table $declaredTable
     * @param Table $generatedTable
     * @param Diff $diff
     * @return void
     */
    public function registerTableModification(Table $declaredTable, Table $generatedTable, Diff $diff)
    {
        if ($declaredTable->getResource() !== $generatedTable->getResource()) {
            $diff->register(
                $declaredTable,
                ReCreateTable::OPERATION_NAME,
                $generatedTable
            );
        } else {
            $diff->register(
                $declaredTable,
                ModifyTable::OPERATION_NAME,
                $generatedTable
            );
        }
    }

    /**
     * Check whether element should be modified or not.
     *
     * @param  ElementInterface $element
     * @param  ElementInterface $generatedElement
     * @return bool
     */
    public function shouldBeModified(
        ElementInterface $element,
        ElementInterface $generatedElement
    ) {
        return !$this->comparator->compare($element, $generatedElement);
    }
}
