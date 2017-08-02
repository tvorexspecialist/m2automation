<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Setup\Lists;

/**
 * Command prints list of available currencies
 * @since 2.0.0
 */
class InfoCurrencyListCommand extends Command
{
    /**
     * List model provides lists of available options for currency, language locales, timezones
     *
     * @var Lists
     * @since 2.0.0
     */
    private $lists;

    /**
     * @param Lists $lists
     * @since 2.0.0
     */
    public function __construct(Lists $lists)
    {
        $this->lists = $lists;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('info:currency:list')
            ->setDescription('Displays the list of available currencies');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(['Currency', 'Code']);

        foreach ($this->lists->getCurrencyList() as $key => $currency) {
            $table->addRow([$currency, $key]);
        }

        $table->render($output);
    }
}
