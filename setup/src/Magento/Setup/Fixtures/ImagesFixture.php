<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImagesFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 51;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Setup\Fixtures\ImagesGenerator\ImagesGeneratorFactory
     */
    private $imagesGeneratorFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $mediaConfig;

    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $dbConnection;

    /**
     * @var \Magento\Framework\DB\Sql\ColumnValueExpressionFactory
     */
    private $expressionFactory;

    /**
     * @var \Magento\Setup\Model\BatchInsertFactory
     */
    private $batchInsertFactory;

    /**
     * @var array
     */
    private $attributeCodesCache = [];

    /**
     * @var int
     */
    private $imagesInsertBatchSize = 1000;

    /**
     * @var int
     */
    private $productsCountCache;

    /**
     * @var array
     */
    private $tableCache = [];

    public function __construct(
        FixtureModel $fixtureModel,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Setup\Fixtures\ImagesGenerator\ImagesGeneratorFactory $imagesGeneratorFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Framework\DB\Sql\ColumnValueExpressionFactory $expressionFactory,
        \Magento\Setup\Model\BatchInsertFactory $batchInsertFactory
    ) {
        parent::__construct($fixtureModel);

        $this->imagesGeneratorFactory = $imagesGeneratorFactory;
        $this->resourceConnection = $resourceConnection;
        $this->filesystem = $filesystem;
        $this->mediaConfig = $mediaConfig;
        $this->attributeRepository = $attributeRepository;
        $this->expressionFactory = $expressionFactory;
        $this->batchInsertFactory = $batchInsertFactory;
    }

    public function execute() {
        $this->createImageEntities();
        $this->assignImagesToProducts();
    }

    public function getActionTitle() {
       return 'Generating images';
    }

    public function introduceParamLabels() {
        return [
            'images' => 'Images'
        ];
    }

    private function createImageEntities()
    {
        /** @var \Magento\Setup\Model\BatchInsert $batchInsert */
        $batchInsert = $this->batchInsertFactory->create([
            'insertIntoTable' => $this->getTable('catalog_product_entity_media_gallery'),
            'batchSize' => $this->imagesInsertBatchSize
        ]);

        foreach ($this->generateImageFilesGenerator() as $imageName) {
            $batchInsert->insert([
                'attribute_id' => $this->getAttributeId('media_gallery'),
                'value' => $imageName,
            ]);
        }

        $batchInsert->flush();
    }

    private function generateImageFilesGenerator()
    {
        /** @var \Magento\Setup\Fixtures\ImagesGenerator\ImagesGenerator $imagesGenerator */
        $imagesGenerator = $this->imagesGeneratorFactory->create();
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $productImagesDirectoryPath = $mediaDirectory->getRelativePath($this->mediaConfig->getBaseMediaPath());

        for ($i = 1; $i <= $this->getImagesToGenerate(); $i++) {
            $imageName = md5($i) . '.jpg';
            $imageFullName = DIRECTORY_SEPARATOR . substr($imageName, 0, 1)
                . DIRECTORY_SEPARATOR . substr($imageName, 1, 1)
                . DIRECTORY_SEPARATOR . $imageName;

            $imagePath = $imagesGenerator->generate([
                'image-width' => 300,
                'image-height' => 300,
                'image-name' => $imageName
            ]);

            $mediaDirectory->renameFile(
                $mediaDirectory->getRelativePath($imagePath),
                $productImagesDirectoryPath . $imageFullName
            );

            yield $imageFullName;
        }
    }

    private function assignImagesToProducts()
    {
        /** @var \Magento\Setup\Model\BatchInsert $batchInsertCatalogProductEntityVarchar */
        $batchInsertCatalogProductEntityVarchar = $this->batchInsertFactory->create([
            'insertIntoTable' => $this->getTable('catalog_product_entity_varchar'),
            'batchSize' => $this->imagesInsertBatchSize
        ]);

        /** @var \Magento\Setup\Model\BatchInsert $batchInsertCatalogProductEntityMediaGalleryValue */
        $batchInsertCatalogProductEntityMediaGalleryValue = $this->batchInsertFactory->create([
            'insertIntoTable' => $this->getTable('catalog_product_entity_media_gallery_value'),
            'batchSize' => $this->imagesInsertBatchSize
        ]);

        /** @var \Magento\Setup\Model\BatchInsert $batchInsertCatalogProductEntityMediaGalleryValueToEntity */
        $batchInsertCatalogProductEntityMediaGalleryValueToEntity = $this->batchInsertFactory->create([
            'insertIntoTable' => $this->getTable('catalog_product_entity_media_gallery_value_to_entity'),
            'batchSize' => $this->imagesInsertBatchSize
        ]);

        $imageGenerator = $this->getImagesGenerator(); // to many images can be selected

        foreach ($this->getProductGenerator() as $productEntityId) {
            for ($imageNum = 1; $imageNum <= $this->getImagesPerProduct(); $imageNum++) {
                $image = $imageGenerator->current();
                $imageGenerator->next();

                if ($imageNum === 1) {
                    $attributes = ['image', 'small_image', 'thumbnail', 'swatch_image'];

                    foreach ($attributes as $attr) {
                        $batchInsertCatalogProductEntityVarchar->insert([
                            'entity_id' => $productEntityId['entity_id'],
                            'attribute_id' => $this->getAttributeId($attr),
                            'value' => $image['value'],
                            'store_id' => 0,
                        ]);
                    }
                }

                $batchInsertCatalogProductEntityMediaGalleryValueToEntity->insert([
                    'value_id' => $image['value_id'],
                    'entity_id' => $productEntityId['entity_id']
                ]);

                $batchInsertCatalogProductEntityMediaGalleryValue->insert([
                    'value_id' => $image['value_id'],
                    'store_id' => 0,
                    'entity_id' => $productEntityId['entity_id'],
                    'position' => $image['value_id'],
                    'disabled' => 0
                ]);
            }
        }

        $batchInsertCatalogProductEntityVarchar->flush();
        $batchInsertCatalogProductEntityMediaGalleryValue->flush();
        $batchInsertCatalogProductEntityMediaGalleryValueToEntity->flush();
    }

    private function getProductGenerator()
    {
        $limit = 1000;
        $offset = 0;

        $products = $this->getProducts($limit, $offset);
        $offset += $limit;

        while (true) {
            yield current($products);

            if (next($products) === false) {
                $products = $this->getProducts($limit, $offset);
                $offset += $limit;

                if (empty($products)) {
                    break;
                }
            }
        }
    }

    private function getProducts($limit, $offset)
    {
        $select = $this->getDbConnection()
            ->select()
            ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
            ->columns(['entity_id'])
            ->limit($limit, $offset);

        return $this->getDbConnection()->fetchAssoc($select);
    }

    private function getImagesGenerator()
    {
        $select = $this->getDbConnection()
            ->select()
            ->from(
                $this->getTable('catalog_product_entity_media_gallery'),
                ['value_id', 'value']
            )->order('value_id desc')
            ->limit($this->getProductsCount() * $this->getImagesPerProduct());

        $images = $this->getDbConnection()->fetchAssoc($select);

        while (true) {
            yield current($images);

            if (next($images) === false) {
                reset($images);
            }
        }
    }

    private function getImagesToGenerate()
    {
        return 100;
    }

    private function getImagesPerProduct()
    {
        return 3;
    }

    private function getProductsCount()
    {
        if ($this->productsCountCache === null) {
            $select = $select = $this->getDbConnection()
                ->select()
                ->from(['product_entity' => $this->getTable('catalog_product_entity')], [])
                ->columns([
                    'count' => $this->expressionFactory->create([
                        'expression' => 'COUNT(1)'
                    ])
                ]);

            $this->productsCountCache = (int) $this->getDbConnection()->fetchOne($select);
        }

        return $this->productsCountCache;
    }

    private function getAttributeId($attributeCode)
    {
        if (!isset($this->attributeCodesCache[$attributeCode])) {
            $attribute = $this->attributeRepository->get(
                'catalog_product',
                $attributeCode
            );

            $this->attributeCodesCache[$attributeCode] = $attribute->getAttributeId();
        }

        return $this->attributeCodesCache[$attributeCode];
    }

    /**
     * Retrieve current connection to DB
     *
     * Method is required to eliminate multiple calls to ResourceConnection class
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getDbConnection()
    {
        if ($this->dbConnection === null) {
            $this->dbConnection = $this->resourceConnection->getConnection();
        }

        return $this->dbConnection;
    }

    /**
     * Retrieve real table name
     *
     * Method act like a cache for already retrieved table names
     * is required to eliminate multiple calls to ResourceConnection class
     *
     * @param string $tableName
     * @return string
     */
    private function getTable($tableName)
    {
        if (!isset($this->tableCache[$tableName])) {
            $this->tableCache[$tableName] = $this->resourceConnection->getTableName($tableName);
        }

        return $this->tableCache[$tableName];
    }

}
