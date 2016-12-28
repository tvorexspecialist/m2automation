<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Model\Config\Structure;

use Magento\Paypal\Model\Config\Structure\PaymentSectionModifier;

class PaymentSectionModifierTest extends \PHPUnit_Framework_TestCase
{
    private static $specialGroups = [
        'account',
        'recommended_solutions',
        'other_paypal_payment_solutions',
        'other_payment_methods',
    ];

    /**
     * @param string $case
     * @param array $structure
     * @dataProvider caseProvider
     */
    public function testSpecialGroupsPresent($case, $structure)
    {
        $modifier = new PaymentSectionModifier();
        $modifiedStructure = $modifier->modify($structure);
        $presentSpecialGroups = array_intersect(
            self::$specialGroups,
            array_keys($modifiedStructure)
        );

        $this->assertEquals(
            self::$specialGroups,
            $presentSpecialGroups,
            sprintf('All special groups must be present in %s case', $case)
        );
    }

    /**
     * @param string $case
     * @param array $structure
     * @dataProvider caseProvider
     */
    public function testOnlySpecialGroupsPresent($case, $structure)
    {
        $modifier = new PaymentSectionModifier();
        $modifiedStructure = $modifier->modify($structure);
        $presentNotSpecialGroups = array_diff(
            array_keys($modifiedStructure),
            self::$specialGroups
        );

        $this->assertEquals(
            [],
            $presentNotSpecialGroups,
            sprintf('Only special groups should be present at top level in "%s" case', $case)
        );
    }

    /**
     * @param string $case
     * @param array $structure
     * @dataProvider caseProvider
     */
    public function testGroupsNotRemovedAfterModification($case, $structure)
    {
        $modifier = new PaymentSectionModifier();
        $modifiedStructure = $modifier->modify($structure);

        $removedGroups = array_diff(
            $this->fetchAllAvailableGroups($structure),
            $this->fetchAllAvailableGroups($modifiedStructure)
        );

        $this->assertEquals(
            [],
            $removedGroups,
            sprintf('Groups should not be removed after modification in "%s" case', $case)
        );
    }

    /**
     * This helper method walk recursively through configuration structure and
     * collect available configuration groups
     *
     * @param array $structure
     * @return array Sorted list of group identifiers
     */
    private function fetchAllAvailableGroups($structure)
    {
        $availableGroups = [];
        foreach ($structure as $group => $data) {
            $availableGroups[] = $group;
            if (isset($data['children'])) {
                $availableGroups = array_merge(
                    $availableGroups,
                    $this->fetchAllAvailableGroups($data['children'])
                );
            }
        }
        $availableGroups = array_values(array_unique($availableGroups));
        sort($availableGroups);
        return $availableGroups;
    }

    public function caseProvider()
    {
        return [
            [
                'empty structure',
                []
            ],
            [
                'structure with special groups at the begin of the list',
                [
                    'account' => [
                        'id' => 'account',
                        'children' => [

                        ]
                    ],
                    'recommended_solutions' => [
                        'id' => 'recommended_solutions',
                        'children' => [

                        ]
                    ],
                    'other_paypal_payment_solutions' => [
                        'id' => 'other_paypal_payment_solutions',
                        'children' => [

                        ]
                    ],
                    'other_payment_methods' => [
                        'id' => 'other_payment_methods',
                        'children' => [

                        ]
                    ],
                    'some_payment_method' => [
                        'id' => 'some_payment_method',
                        'children' => [

                        ]
                    ],
                ]
            ],
            [
                'structure with special groups at the end of the list',
                [
                    'some_payment_method' => [
                        'id' => 'some_payment_method',
                        'children' => [

                        ]
                    ],
                    'account' => [
                        'id' => 'account',
                        'children' => [

                        ]
                    ],
                    'recommended_solutions' => [
                        'id' => 'recommended_solutions',
                        'children' => [

                        ]
                    ],
                    'other_paypal_payment_solutions' => [
                        'id' => 'other_paypal_payment_solutions',
                        'children' => [

                        ]
                    ],
                    'other_payment_methods' => [
                        'id' => 'other_payment_methods',
                        'children' => [

                        ]
                    ],
                ]
            ],
            [
                'structure with special groups in the middle of the list',
                [
                    'some_payment_methodq' => [
                        'id' => 'some_payment_methodq',
                        'children' => [

                        ]
                    ],
                    'account' => [
                        'id' => 'account',
                        'children' => [

                        ]
                    ],
                    'recommended_solutions' => [
                        'id' => 'recommended_solutions',
                        'children' => [

                        ]
                    ],
                    'other_paypal_payment_solutions' => [
                        'id' => 'other_paypal_payment_solutions',
                        'children' => [

                        ]
                    ],
                    'other_payment_methods' => [
                        'id' => 'other_payment_methods',
                        'children' => [

                        ]
                    ],
                    'some_payment_method2' => [
                        'id' => 'some_payment_method2',
                        'children' => [

                        ]
                    ],
                ]
            ],
            [
                'structure with all assigned groups',
                [
                    'some_payment_method1' => [
                        'id' => 'some_payment_method1',
                        'displayIn' => 'other_paypal_payment_solutions',
                    ],
                    'some_payment_method2' => [
                        'id' => 'some_payment_method2',
                        'displayIn' => 'recommended_solutions',
                    ],
                ]
            ],
            [
                'structure with not assigned groups',
                [
                    'some_payment_method1' => [
                        'id' => 'some_payment_method1',
                        'displayIn' => 'other_paypal_payment_solutions',
                    ],
                    'some_payment_method2' => [
                        'id' => 'some_payment_method2',
                    ],
                ]
            ],
            [
                'special groups has predefined children',
                [
                    'recommended_solutions' => [
                        'id' => 'recommended_solutions',
                        'children' => [
                            'some_payment_method1' => [
                                'id' => 'some_payment_method1',
                            ],
                        ]
                    ],
                    'some_payment_method2' => [
                        'id' => 'some_payment_method2',
                        'displayIn' => 'recommended_solutions',
                    ],
                ]
            ]
        ];
    }
}
