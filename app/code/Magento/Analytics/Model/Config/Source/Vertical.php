<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Config\Source;

/**
 * A source model for verticals configuration.
 *
 * Prepares and provides options for a selector of verticals which is located
 * in the corresponding configuration menu of the Admin area.
 * @since 2.2.0
 */
class Vertical implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * The list of possible verticals.
     *
     * This list is configured via di.xml and may be extended or changed
     * in any module if it is needed.
     *
     * It is supposed that the list may be changed in each Magento release.
     *
     * @var array
     * @since 2.2.0
     */
    private $verticals;

    /**
     * @param array $verticals
     * @since 2.2.0
     */
    public function __construct(array $verticals)
    {
        $this->verticals = $verticals;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function toOptionArray()
    {
        $result = [
            ['value' => '', 'label' => __('--Please Select--')]
        ];

        foreach ($this->verticals as $vertical) {
            $result[] = ['value' => $vertical, 'label' => __($vertical)];
        }

        return $result;
    }
}
