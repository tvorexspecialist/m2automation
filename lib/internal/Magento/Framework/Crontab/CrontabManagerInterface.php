<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Crontab;

interface CrontabManagerInterface
{
    const TASKS_BLOCK_START = '#~ MAGENTO START';
    const TASKS_BLOCK_END = '#~ MAGENTO END';

    /**
     * Get list of Magento Tasks
     *
     * @return array
     * @throws \Exception
     */
    public function getTasks();

    /**
     * Save Magento Tasks to crontab
     *
     * @param array $tasks
     * @return void
     * @throws \Exception
     */
    public function saveTasks(array $tasks);

    /**
     * Remove Magento Tasks form crontab
     *
     * @return void
     * @throws \Exception
     */
    public function removeTasks();
}
