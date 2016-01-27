<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class SelectRenderer
 */
class SelectRenderer implements RendererInterface
{
    /**
     * @var RendererInterface[]
     */
    protected $renders;

    /**
     * @param RendererInterface[] $renderers
     */
    public function __construct(
        array $renderers
    ) {
        $this->renders = $this->sort($renderers);
    }

    /**
     * Sort renders
     *
     * @param array $renders
     * @return array
     */
    protected function sort($renders)
    {
        $length = count($renders);
        if ($length <= 1) {
            return $renders;
        } else {
            $pivot = array_shift($renders);
            $left = $right = [];
            foreach ($renders as $render) {
                if ($render['sort'] < $pivot['sort']) {
                    $left[] = $render;
                } else {
                    $right[] = $render;
                }
            }

            return array_merge(
                $this->sort($left),
                [$pivot],
                $this->sort($right)
            );
        }
    }

    /**
     * Render SELECT statement
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        $sql = Select::SQL_SELECT;
        foreach ($this->renders as $renderer) {
            $sql = $renderer['renderer']->render($select, $sql);
        }
        return $sql;
    }
}
