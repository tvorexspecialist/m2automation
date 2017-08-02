<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task\Operation;

use Magento\Setup\Module\Di\App\Task\OperationInterface;
use Magento\Framework\App;
use Magento\Setup\Module\Di\Compiler\Config;
use Magento\Setup\Module\Di\Definition\Collection as DefinitionsCollection;

/**
 * Class \Magento\Setup\Module\Di\App\Task\Operation\Area
 *
 * @since 2.0.0
 */
class Area implements OperationInterface
{
    /**
     * @var App\AreaList
     * @since 2.0.0
     */
    private $areaList;

    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\Decorator\Area
     * @since 2.0.0
     */
    private $areaInstancesNamesList;

    /**
     * @var Config\Reader
     * @since 2.0.0
     */
    private $configReader;

    /**
     * @var Config\WriterInterface
     * @since 2.0.0
     */
    private $configWriter;

    /**
     * @var array
     * @since 2.0.0
     */
    private $data = [];

    /**
     * @var \Magento\Setup\Module\Di\Compiler\Config\ModificationChain
     * @since 2.0.0
     */
    private $modificationChain;

    /**
     * @param App\AreaList $areaList
     * @param \Magento\Setup\Module\Di\Code\Reader\Decorator\Area $areaInstancesNamesList
     * @param Config\Reader $configReader
     * @param Config\WriterInterface $configWriter
     * @param \Magento\Setup\Module\Di\Compiler\Config\ModificationChain $modificationChain
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        App\AreaList $areaList,
        \Magento\Setup\Module\Di\Code\Reader\Decorator\Area $areaInstancesNamesList,
        Config\Reader $configReader,
        Config\WriterInterface $configWriter,
        Config\ModificationChain $modificationChain,
        $data = []
    ) {
        $this->areaList = $areaList;
        $this->areaInstancesNamesList = $areaInstancesNamesList;
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
        $this->data = $data;
        $this->modificationChain = $modificationChain;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function doOperation()
    {
        if (empty($this->data)) {
            return;
        }

        $definitionsCollection = new DefinitionsCollection();
        foreach ($this->data as $paths) {
            if (!is_array($paths)) {
                $paths = (array)$paths;
            }
            foreach ($paths as $path) {
                $definitionsCollection->addCollection($this->getDefinitionsCollection($path));
            }
        }

        $areaCodes = array_merge([App\Area::AREA_GLOBAL], $this->areaList->getCodes());
        foreach ($areaCodes as $areaCode) {
            $config = $this->configReader->generateCachePerScope($definitionsCollection, $areaCode);
            $config = $this->modificationChain->modify($config);

            $this->configWriter->write(
                $areaCode,
                $config
            );
        }
    }

    /**
     * Returns definitions collection
     *
     * @param string $path
     * @return DefinitionsCollection
     * @since 2.0.0
     */
    protected function getDefinitionsCollection($path)
    {
        $definitions = new DefinitionsCollection();
        foreach ($this->areaInstancesNamesList->getList($path) as $className => $constructorArguments) {
            $definitions->addDefinition($className, $constructorArguments);
        }
        return $definitions;
    }

    /**
     * Returns operation name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return 'Area configuration aggregation';
    }
}
