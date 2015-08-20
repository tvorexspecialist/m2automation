<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Catalog image helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Image extends AbstractHelper
{
    /**
     * Current model
     *
     * @var \Magento\Catalog\Model\Product\Image
     */
    protected $_model;

    /**
     * Scheduled for resize image
     *
     * @var bool
     */
    protected $_scheduleResize = true;

    /**
     * Scheduled for rotate image
     *
     * @var bool
     */
    protected $_scheduleRotate = false;

    /**
     * Angle
     *
     * @var int
     */
    protected $_angle;

    /**
     * Watermark file name
     *
     * @var string
     */
    protected $_watermark;

    /**
     * Watermark Position
     *
     * @var string
     */
    protected $_watermarkPosition;

    /**
     * Watermark Size
     *
     * @var string
     */
    protected $_watermarkSize;

    /**
     * Watermark Image opacity
     *
     * @var int
     */
    protected $_watermarkImageOpacity;

    /**
     * Current Product
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Image File
     *
     * @var string
     */
    protected $_imageFile;

    /**
     * Image Placeholder
     *
     * @var string
     */
    protected $_placeholder;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * Product image factory
     *
     * @var \Magento\Catalog\Model\Product\ImageFactory
     */
    protected $_productImageFactory;

    /**
     * @var \Magento\Framework\View\ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var \Magento\Framework\Config\View
     */
    protected $configView;

    /**
     * Image configuration attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product\ImageFactory $productImageFactory
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product\ImageFactory $productImageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig
    ) {
        $this->_productImageFactory = $productImageFactory;
        parent::__construct($context);
        $this->_assetRepo = $assetRepo;
        $this->viewConfig = $viewConfig;
    }

    /**
     * Reset all previous data
     *
     * @return $this
     */
    protected function _reset()
    {
        $this->_model = null;
        $this->_scheduleRotate = false;
        $this->_angle = null;
        $this->_watermark = null;
        $this->_watermarkPosition = null;
        $this->_watermarkSize = null;
        $this->_watermarkImageOpacity = null;
        $this->_product = null;
        $this->_imageFile = null;
        $this->attributes = [];
        return $this;
    }

    /**
     * Initialize Helper to work with Image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return $this
     */
    public function init($product, $imageId, $attributes = [])
    {
        $this->_reset();

        $this->attributes = array_merge(
            $attributes,
            $this->getConfigView()->getImageAttributes('Magento_Catalog', $imageId)
        );

        $this->setProduct($product);
        $this->setImageProperties();
        $this->setWatermarkProperties();

        return $this;
    }

    /**
     * Set image properties
     *
     * @return $this
     */
    protected function setImageProperties()
    {
        $this->_getModel()->setDestinationSubdir($this->getType());

        $this->_getModel()->setWidth($this->getWidth());
        $this->_getModel()->setHeight($this->getHeight());

        // Set 'keep frame' flag
        $frame = $this->getFrame();
        if (!empty($frame)) {
            $this->_getModel()->setKeepFrame($frame);
        }

        // Set 'constrain only' flag
        $constrain = $this->getAttribute('constrain');
        if (!empty($constrain)) {
            $this->_getModel()->setConstrainOnly($constrain);
        }

        // Set 'keep aspect ratio' flag
        $aspectRatio = $this->getAttribute('aspect_ratio');
        if (!empty($aspectRatio)) {
            $this->_getModel()->setKeepAspectRatio($aspectRatio);
        }

        // Set 'transparency' flag
        $transparency = $this->getAttribute('transparency');
        if (!empty($transparency)) {
            $this->_getModel()->setKeepTransparency($transparency);
        }

        // Set background color
        $background = $this->getAttribute('background');
        if (!empty($background)) {
            $this->_getModel()->setBackgroundColor($background);
        }

        return $this;
    }

    /**
     * Set watermark properties
     *
     * @return $this
     */
    protected function setWatermarkProperties()
    {
        $this->setWatermark(
            $this->scopeConfig->getValue(
                "design/watermark/{$this->_getModel()->getDestinationSubdir()}_image",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $this->setWatermarkImageOpacity(
            $this->scopeConfig->getValue(
                "design/watermark/{$this->_getModel()->getDestinationSubdir()}_imageOpacity",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $this->setWatermarkPosition(
            $this->scopeConfig->getValue(
                "design/watermark/{$this->_getModel()->getDestinationSubdir()}_position",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $this->setWatermarkSize(
            $this->scopeConfig->getValue(
                "design/watermark/{$this->_getModel()->getDestinationSubdir()}_size",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        return $this;
    }

    /**
     * Schedule resize of the image
     * $width *or* $height can be null - in this case, lacking dimension will be calculated.
     *
     * @see \Magento\Catalog\Model\Product\Image
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function resize($width, $height = null)
    {
        $this->_getModel()->setWidth($width)->setHeight($height);
        $this->_scheduleResize = true;
        return $this;
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     *
     * @param int $quality
     * @return $this
     */
    public function setQuality($quality)
    {
        $this->_getModel()->setQuality($quality);
        return $this;
    }

    /**
     * Guarantee, that image picture width/height will not be distorted.
     * Applicable before calling resize()
     * It is true by default.
     *
     * @see \Magento\Catalog\Model\Product\Image
     * @param bool $flag
     * @return $this
     */
    public function keepAspectRatio($flag)
    {
        $this->_getModel()->setKeepAspectRatio($flag);
        return $this;
    }

    /**
     * Guarantee, that image will have dimensions, set in $width/$height
     * Applicable before calling resize()
     * Not applicable, if keepAspectRatio(false)
     *
     * $position - TODO, not used for now - picture position inside the frame.
     *
     * @see \Magento\Catalog\Model\Product\Image
     * @param bool $flag
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function keepFrame($flag)
    {
        $this->_getModel()->setKeepFrame($flag);
        return $this;
    }

    /**
     * Guarantee, that image will not lose transparency if any.
     * Applicable before calling resize()
     * It is true by default.
     *
     * $alphaOpacity - TODO, not used for now
     *
     * @see \Magento\Catalog\Model\Product\Image
     * @param bool $flag
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function keepTransparency($flag)
    {
        $this->_getModel()->setKeepTransparency($flag);
        return $this;
    }

    /**
     * Guarantee, that image picture will not be bigger, than it was.
     * Applicable before calling resize()
     * It is false by default
     *
     * @param bool $flag
     * @return $this
     */
    public function constrainOnly($flag)
    {
        $this->_getModel()->setConstrainOnly($flag);
        return $this;
    }

    /**
     * Set color to fill image frame with.
     * Applicable before calling resize()
     * The keepTransparency(true) overrides this (if image has transparent color)
     * It is white by default.
     *
     * @see \Magento\Catalog\Model\Product\Image
     * @param array $colorRGB
     * @return $this
     */
    public function backgroundColor($colorRGB)
    {
        // assume that 3 params were given instead of array
        if (!is_array($colorRGB)) {
            $colorRGB = func_get_args();
        }
        $this->_getModel()->setBackgroundColor($colorRGB);
        return $this;
    }

    /**
     * Rotate image into specified angle
     *
     * @param int $angle
     * @return $this
     */
    public function rotate($angle)
    {
        $this->setAngle($angle);
        $this->_getModel()->setAngle($angle);
        $this->_scheduleRotate = true;
        return $this;
    }

    /**
     * Add watermark to image
     * size param in format 100x200
     *
     * @param string $fileName
     * @param string $position
     * @param string $size
     * @param int $imageOpacity
     * @return $this
     */
    public function watermark($fileName, $position, $size = null, $imageOpacity = null)
    {
        $this->setWatermark(
            $fileName
        )->setWatermarkPosition(
            $position
        )->setWatermarkSize(
            $size
        )->setWatermarkImageOpacity(
            $imageOpacity
        );
        return $this;
    }

    /**
     * Set placeholder
     *
     * @param string $fileName
     * @return void
     */
    public function placeholder($fileName)
    {
        $this->_placeholder = $fileName;
    }

    /**
     * Get Placeholder
     * @param null|string $placeholder
     * @return string
     */
    public function getPlaceholder($placeholder = null)
    {
        if (!$this->_placeholder) {
            $placeholder = $placeholder?: $this->_getModel()->getDestinationSubdir();
            $this->_placeholder = 'Magento_Catalog::images/product/placeholder/' . $placeholder . '.jpg';
        }
        return $this->_placeholder;
    }

    /**
     * Apply scheduled actions
     *
     * @return $this
     * @throws \Exception
     */
    protected function applyScheduledActions()
    {
        $model = $this->_getModel();
        if ($this->getImageFile()) {
            $model->setBaseFile($this->getImageFile());
        } else {
            $model->setBaseFile($this->getProduct()->getData($model->getDestinationSubdir()));
        }

        if (!$model->isCached()) {
            if ($this->_scheduleRotate) {
                $model->rotate($this->getAngle());
            }
            if ($this->_scheduleResize) {
                $model->resize();
            }
            if ($this->getWatermark()) {
                $model->setWatermark($this->getWatermark());
            }
            $model->saveFile();
        }
        return $this;
    }

    /**
     * Retrieve image URL
     *
     * @return string
     */
    public function getUrl()
    {
        try {
            $this->applyScheduledActions();
            return $this->_getModel()->getUrl();
        } catch (\Exception $e) {
            return $this->getDefaultPlaceholderUrl();
        }
    }

    /**
     * @return $this
     */
    public function save()
    {
        $this->applyScheduledActions();
        return $this;
    }

    /**
     * Return resized product image information
     *
     * @return array
     */
    public function getResizedImageInfo()
    {
        $this->applyScheduledActions();
        return $this->_getModel()->getResizedImageInfo();
    }

    /**
     * @param null|string $placeholder
     * @return string
     */
    public function getDefaultPlaceholderUrl($placeholder = null)
    {
        try {
            $url = $this->_assetRepo->getUrl($this->getPlaceholder($placeholder));
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $url = $this->_urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
        return $url;
    }

    /**
     * Get current Image model
     *
     * @return \Magento\Catalog\Model\Product\Image
     */
    protected function _getModel()
    {
        if (!$this->_model) {
            $this->_model = $this->_productImageFactory->create();
        }
        return $this->_model;
    }

    /**
     * Set Rotation Angle
     *
     * @param int $angle
     * @return $this
     */
    protected function setAngle($angle)
    {
        $this->_angle = $angle;
        return $this;
    }

    /**
     * Get Rotation Angle
     *
     * @return int
     */
    protected function getAngle()
    {
        return $this->_angle;
    }

    /**
     * Set watermark file name
     *
     * @param string $watermark
     * @return $this
     */
    protected function setWatermark($watermark)
    {
        $this->_watermark = $watermark;
        $this->_getModel()->setWatermarkFile($watermark);
        return $this;
    }

    /**
     * Get watermark file name
     *
     * @return string
     */
    protected function getWatermark()
    {
        return $this->_watermark;
    }

    /**
     * Set watermark position
     *
     * @param string $position
     * @return $this
     */
    protected function setWatermarkPosition($position)
    {
        $this->_watermarkPosition = $position;
        $this->_getModel()->setWatermarkPosition($position);
        return $this;
    }

    /**
     * Get watermark position
     *
     * @return string
     */
    protected function getWatermarkPosition()
    {
        return $this->_watermarkPosition;
    }

    /**
     * Set watermark size
     * param size in format 100x200
     *
     * @param string $size
     * @return $this
     */
    public function setWatermarkSize($size)
    {
        $this->_watermarkSize = $size;
        $this->_getModel()->setWatermarkSize($this->parseSize($size));
        return $this;
    }

    /**
     * Get watermark size
     *
     * @return string
     */
    protected function getWatermarkSize()
    {
        return $this->_watermarkSize;
    }

    /**
     * Set watermark image opacity
     *
     * @param int $imageOpacity
     * @return $this
     */
    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->_watermarkImageOpacity = $imageOpacity;
        $this->_getModel()->setWatermarkImageOpacity($imageOpacity);
        return $this;
    }

    /**
     * Get watermark image opacity
     *
     * @return int
     */
    protected function getWatermarkImageOpacity()
    {
        if ($this->_watermarkImageOpacity) {
            return $this->_watermarkImageOpacity;
        }

        return $this->_getModel()->getWatermarkImageOpacity();
    }

    /**
     * Set current Product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    protected function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Get current Product
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct()
    {
        return $this->_product;
    }

    /**
     * Set Image file
     *
     * @param string $file
     * @return $this
     */
    public function setImageFile($file)
    {
        $this->_imageFile = $file;
        return $this;
    }

    /**
     * Get Image file
     *
     * @return string
     */
    protected function getImageFile()
    {
        return $this->_imageFile;
    }

    /**
     * Retrieve size from string
     *
     * @param string $string
     * @return array|bool
     */
    protected function parseSize($string)
    {
        $size = explode('x', strtolower($string));
        if (sizeof($size) == 2) {
            return ['width' => $size[0] > 0 ? $size[0] : null, 'height' => $size[1] > 0 ? $size[1] : null];
        }
        return false;
    }

    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->_getModel()->getImageProcessor()->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->_getModel()->getImageProcessor()->getOriginalHeight();
    }

    /**
     * Retrieve Original image size as array
     * 0 - width, 1 - height
     *
     * @return int[]
     */
    public function getOriginalSizeArray()
    {
        return [$this->getOriginalWidth(), $this->getOriginalHeight()];
    }

    /**
     * Retrieve config view
     *
     * @return \Magento\Framework\Config\View
     */
    protected function getConfigView()
    {
        if (!$this->configView) {
            $this->configView = $this->viewConfig->getViewConfig();
        }
        return $this->configView;
    }

    /**
     * Retrieve image type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }

    /**
     * Retrieve image width
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->getAttribute('width');
    }

    /**
     * Retrieve image height
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->getAttribute('height') ?: $this->getAttribute('width');
    }

    /**
     * Retrieve image frame flag
     *
     * @return false|string
     */
    public function getFrame()
    {
        $frame = $this->getAttribute('frame');
        if (empty($frame)) {
            $frame = $this->getConfigView()->getVarValue('Magento_Catalog', 'product_image_white_borders');
        }
        return $frame;
    }

    /**
     * Retrieve image attribute
     *
     * @param string $name
     * @return string
     */
    protected function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * Return image label
     *
     * @return string
     */
    public function getLabel()
    {
        $label = $this->_product->getData($this->getType() . '_' . 'label');
        if (empty($label)) {
            $label = $this->_product->getName();
        }
        return $label;
    }
}
