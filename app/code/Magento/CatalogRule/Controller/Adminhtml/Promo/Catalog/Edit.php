<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

class Edit extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
{
    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var \Magento\CatalogRule\Model\Rule $model */
        $model = $this->_objectManager->create('Magento\CatalogRule\Model\Rule');

        /** @var \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface $ruleRepository */
        $ruleRepository = $this->_objectManager->create(
            'Magento\CatalogRule\Api\CatalogRuleRepositoryInterface'
        );

        if ($id) {
            try {
                $model = $ruleRepository->get($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                $this->_redirect('catalog_rule/*');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        $this->_coreRegistry->register('current_promo_catalog_rule', $model);

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Catalog Price Rule'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getRuleId() ? $model->getName() : __('New Catalog Price Rule')
        );
        $this->_view->getLayout()
            ->getBlock('promo_catalog_edit')
            ->setData('action', $this->getUrl('catalog_rule/promo_catalog/save'));

        $breadcrumb = $id ? __('Edit Rule') : __('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }
}
