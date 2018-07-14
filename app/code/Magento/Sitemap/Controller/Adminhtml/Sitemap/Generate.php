<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
use Magento\Store\Model\App\Emulation;

class Generate extends \Magento\Sitemap\Controller\Adminhtml\Sitemap
{
    /** @var \Magento\Store\Model\App\Emulation $appEmulation */
    private $appEmulation;

    /**
     * Generate constructor.
     * @param Action\Context $context
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     */
    public function __construct(
        Action\Context $context,
        Emulation $appEmulation
    ) {
        $this->appEmulation = $appEmulation;
        parent::__construct(
            $context
        );
    }

    /**
     * Generate sitemap
     *
     * @return void
     */
    public function execute()
    {
        // init and load sitemap model
        $id = $this->getRequest()->getParam('sitemap_id');
        $sitemap = $this->_objectManager->create(\Magento\Sitemap\Model\Sitemap::class);
        /* @var $sitemap \Magento\Sitemap\Model\Sitemap */
        $sitemap->load($id);
        // if sitemap record exists
        if ($sitemap->getId()) {
            try {
                //We need to emulate to get the correct frontend URL for the product images
                $this->appEmulation->startEnvironmentEmulation(
                    $sitemap->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                $sitemap->generateXml();
                $this->appEmulation->stopEnvironmentEmulation();

                $this->messageManager->addSuccess(
                    __('The sitemap "%1" has been generated.', $sitemap->getSitemapFilename())
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t generate the sitemap right now.'));
            }
        } else {
            $this->messageManager->addError(__('We can\'t find a sitemap to generate.'));
        }

        // go to grid
        $this->_redirect('adminhtml/*/');
    }
}
