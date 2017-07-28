<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Page;

/**
 * Adminhtml footer block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Footer extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'page/footer.phtml';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 2.1.0
     */
    protected $productMetadata;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->setShowProfiler(true);
    }

    /**
     * Get product version
     *
     * @return string
     * @since 2.1.0
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
