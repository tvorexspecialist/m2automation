<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product gallery
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection;

/**
 * @api
 * @since 2.0.0
 */
class Gallery extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getProduct()->getMetaTitle());
        return parent::_prepareLayout();
    }

    /**
     * @return Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * @return Collection
     * @since 2.0.0
     */
    public function getGalleryCollection()
    {
        return $this->getProduct()->getMediaGalleryImages();
    }

    /**
     * @return Image|null
     * @since 2.0.0
     */
    public function getCurrentImage()
    {
        $imageId = $this->getRequest()->getParam('image');
        $image = null;
        if ($imageId) {
            $image = $this->getGalleryCollection()->getItemById($imageId);
        }

        if (!$image) {
            $image = $this->getGalleryCollection()->getFirstItem();
        }
        return $image;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getImageUrl()
    {
        return $this->getCurrentImage()->getUrl();
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getImageFile()
    {
        return $this->getCurrentImage()->getFile();
    }

    /**
     * Retrieve image width
     *
     * @return bool|int
     * @since 2.0.0
     */
    public function getImageWidth()
    {
        $file = $this->getCurrentImage()->getPath();

        if ($this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->isFile($file)) {
            $size = getimagesize($file);
            if (isset($size[0])) {
                if ($size[0] > 600) {
                    return 600;
                } else {
                    return $size[0];
                }
            }
        }

        return false;
    }

    /**
     * @return Image|false
     * @since 2.0.0
     */
    public function getPreviousImage()
    {
        $current = $this->getCurrentImage();
        if (!$current) {
            return false;
        }
        $previous = false;
        foreach ($this->getGalleryCollection() as $image) {
            if ($image->getValueId() == $current->getValueId()) {
                return $previous;
            }
            $previous = $image;
        }
        return $previous;
    }

    /**
     * @return Image|false
     * @since 2.0.0
     */
    public function getNextImage()
    {
        $current = $this->getCurrentImage();
        if (!$current) {
            return false;
        }

        $next = false;
        $currentFind = false;
        foreach ($this->getGalleryCollection() as $image) {
            if ($currentFind) {
                return $image;
            }
            if ($image->getValueId() == $current->getValueId()) {
                $currentFind = true;
            }
        }
        return $next;
    }

    /**
     * @return false|string
     * @since 2.0.0
     */
    public function getPreviousImageUrl()
    {
        $image = $this->getPreviousImage();
        if ($image) {
            return $this->getUrl('*/*/*', ['_current' => true, 'image' => $image->getValueId()]);
        }
        return false;
    }

    /**
     * @return false|string
     * @since 2.0.0
     */
    public function getNextImageUrl()
    {
        $image = $this->getNextImage();
        if ($image) {
            return $this->getUrl('*/*/*', ['_current' => true, 'image' => $image->getValueId()]);
        }
        return false;
    }
}
