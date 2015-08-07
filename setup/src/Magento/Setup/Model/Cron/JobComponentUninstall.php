<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Setup\Model\ModuleUninstaller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Job to remove a component. Run by Setup Cron Task
 */
class JobComponentUninstall extends AbstractJob
{
    /**
     * Component type
     */
    const COMPONENT_TYPE = 'type';

    /**
     * Component name
     */
    const COMPONENT_NAME = 'name';

    const COMPONENT_MODULE = 'module';
    const COMPONENT_THEME = 'theme';
    const COMPONENT_LANGUAGE = 'language';

    /**
     * @var ComponentUninstallerFactory
     */
    private $componentUninstallerFactory;

    /**
     * Constructor
     *
     * @param ComponentUninstallerFactory $componentUninstallerFactory
     * @param OutputInterface $output
     * @param Status $status
     * @param array $name
     * @param array $params
     */
    public function __construct(
        ComponentUninstallerFactory $componentUninstallerFactory,
        OutputInterface $output,
        Status $status,
        $name,
        $params = []
    ) {
        $this->componentUninstallerFactory = $componentUninstallerFactory;
        parent::__construct($output, $status, $name, $params);
    }

    /**
     * Run remove component job
     *
     * @return void
     * @throw \RuntimeException
     */
    public function execute()
    {
        if (!isset($this->params[self::COMPONENT_TYPE])
            || !isset($this->params[self::COMPONENT_NAME])
            || !is_array($this->params[self::COMPONENT_NAME])
        ) {
            throw new \RuntimeException('Job parameter format is incorrect');
        }
        $type = $this->params[self::COMPONENT_TYPE];
        $component = $this->params[self::COMPONENT_NAME];

        if (!in_array($type, [self::COMPONENT_MODULE, self::COMPONENT_THEME, self::COMPONENT_LANGUAGE])) {
            throw new \RuntimeException('Unknown component type');
        }

        $options = [];
        switch ($type) {
            case self::COMPONENT_MODULE:
                $options[ModuleUninstaller::OPTION_REMOVE_DATA] = true;
                $options[ModuleUninstaller::OPTION_REMOVE_REGISTRY] = true;
                break;
            case self::COMPONENT_THEME:
                break;
            case self::COMPONENT_LANGUAGE:
                break;
        }
        $this->createAndRunUninstaller($type, $component, $options);
    }

    /**
     * Create the command and run it
     *
     * @param $type
     * @param array $component
     * @param array $options
     * @return void
     */
    private function createAndRunUninstaller($type, array $component, array $options)
    {
        $uninstaller = $this->componentUninstallerFactory->create($type);
        $uninstaller->uninstall($this->output, $component, $options);
    }
}
