<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Theme\Model\Resource\Theme\Collection as ThemeCollection;
use Magento\Framework\App\Area;
use Magento\Framework\View\ConfigInterface;

class Cache
{
    /**
     * @var ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var ThemeCollection
     */
    protected $themeCollection;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param ConfigInterface $viewConfig
     * @param ThemeCollection $themeCollection
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        ConfigInterface $viewConfig,
        ThemeCollection $themeCollection,
        ImageHelper $imageHelper
    ) {
        $this->viewConfig = $viewConfig;
        $this->themeCollection = $themeCollection;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Retrieve view configuration data
     *
     * Collect data for 'Magento_Catalog' module from /etc/view.xml files.
     *
     * @return array
     */
    protected function getData()
    {
        if (!$this->data) {
            /** @var \Magento\Theme\Model\Theme $theme */
            foreach ($this->themeCollection->loadRegisteredThemes() as $theme) {
                $config = $this->viewConfig->getViewConfig([
                    'area' => Area::AREA_FRONTEND,
                    'themeModel' => $theme,
                ]);
                $images = $config->getImages('Magento_Catalog');
                foreach ($images as $imageId => $imageData) {
                    $this->data[$theme->getCode() . $imageId] = array_merge(['id' => $imageId], $imageData);
                }
            }
        }
        return $this->data;
    }

    /**
     * Resize product images and save results to image cache
     *
     * @param Product $product
     * @return $this
     */
    public function generate(Product $product)
    {
        $galleryImages = $product->getMediaGalleryImages();
        if ($galleryImages) {
            foreach ($galleryImages as $image) {
                foreach ($this->getData() as $imageData) {
                    $this->imageHelper->init($product, $imageData['id'], $imageData);
                    $this->imageHelper->setImageFile($image->getFile());

                    if (isset($imageData['aspect_ratio'])) {
                        $this->imageHelper->keepAspectRatio($imageData['aspect_ratio']);
                    }
                    if (isset($imageData['frame'])) {
                        $this->imageHelper->keepFrame($imageData['frame']);
                    }
                    if (isset($imageData['transparency'])) {
                        $this->imageHelper->keepTransparency($imageData['transparency']);
                    }
                    if (isset($imageData['constrain'])) {
                        $this->imageHelper->constrainOnly($imageData['constrain']);
                    }
                    if (isset($imageData['background'])) {
                        $this->imageHelper->keepAspectRatio($imageData['background']);
                    }

                    if (isset($imageData['width']) || isset($imageData['height'])) {
                        $this->imageHelper->resize($imageData['width'], $imageData['height']);
                    }
                    $this->imageHelper->save();
                }
            }
        }
        return $this;
    }
}
