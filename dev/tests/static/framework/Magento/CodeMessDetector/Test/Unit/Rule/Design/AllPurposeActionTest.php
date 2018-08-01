<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CodeMessDetector\Test\Unit\Rule\Design;

use Magento\CodeMessDetector\Rule\Design\AllPurposeAction;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ActionInterface;
use PDepend\Source\AST\ASTArtifactList;
use PDepend\Source\AST\ASTInterface;
use PHPUnit\Framework\TestCase as TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker as InvocationMocker;
use PHPMD\Report;
use PHPMD\Node\ClassNode;

class AllPurposeActionTest extends TestCase
{
    /**
     * @param array $interfaces
     * @param bool $violates
     *
     * @dataProvider getCases
     */
    public function testApply(array $interfaces, bool $violates)
    {
        $node = $this->createNodeMock($interfaces);
        $rule = new AllPurposeAction();
        $this->expectsRuleViolation($rule, $violates);
        $rule->apply($node);
    }

    /**
     * @return array
     */
    public function getCases(): array
    {
        return [
            [[ActionInterface::class, HttpGetActionInterface::class], false],
            [[ActionInterface::class], true],
            [[HttpGetActionInterface::class], false],
        ];
    }

    /**
     * @param string[] $interfaces
     * @return ClassNode|MockObject
     */
    private function createNodeMock(array $interfaces): MockObject
    {
        $interfaceNodes = [];
        foreach ($interfaces as $interface) {
            $interfaceNode = $this->getMockBuilder(ASTInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(['getNamespacedName'])
                ->getMock();
            $interfaceNode->expects($this->any())
                ->method('getNamespacedName')
                ->willReturn($interface);
            $interfaceNodes[] = $interfaceNode;
        }
        $node = $this->getMockBuilder(ClassNode::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->setMethods([
                'getInterfaces',
                // disable name lookup from AST artifact
                'getNamespaceName',
                'getParentName',
                'getName',
            ])
            ->getMock();
        $node->expects($this->any())
            ->method('getInterfaces')
            ->willReturn(new ASTArtifactList($interfaceNodes));

        return $node;
    }

    /**
     * @param AllPurposeAction $rule
     * @param bool $expects
     * @return InvocationMocker
     */
    private function expectsRuleViolation(
        AllPurposeAction $rule,
        bool $expects
    ): InvocationMocker {
        /** @var Report|MockObject $report */
        $report = $this->getMockBuilder(Report::class)->getMock();
        if ($expects) {
            $violationExpectation = $this->atLeastOnce();
        } else {
            $violationExpectation = $this->never();
        }
        $invokation = $report->expects($violationExpectation)
            ->method('addRuleViolation');
        $rule->setReport($report);

        return $invokation;
    }
}
