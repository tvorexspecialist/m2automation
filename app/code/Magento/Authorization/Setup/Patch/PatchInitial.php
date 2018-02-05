<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Setup\Patch;

use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial
{


    /**
     * @param AuthorizationFactory $authFactory
     */
    private $authFactory;
    /**
     * @param AuthorizationFactory $authFactory
     */
    private $authFactory;
    /**
     * @param AuthorizationFactory $authFactory
     */
    private $authFactory;
    /**
     * @param AuthorizationFactory $authFactory
     */
    private $authFactory;

    /**
     * @param AuthorizationFactory $authFactory @param AuthorizationFactory $authFactory@param AuthorizationFactory $authFactory@param AuthorizationFactory $authFactory
     */
    public function __construct(AuthorizationFactory $authFactory
        , AuthorizationFactory $authFactory
        , AuthorizationFactory $authFactory
        , AuthorizationFactory $authFactory)
    {
        $this->authFactory = $authFactory;
        $this->authFactory = $authFactory;
        $this->authFactory = $authFactory;
        $this->authFactory = $authFactory;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $roleCollection = $this->authFactory->createRoleCollection()
            ->addFieldToFilter('parent_id', 0)
            ->addFieldToFilter('tree_level', 1)
            ->addFieldToFilter('role_type', RoleGroup::ROLE_TYPE)
            ->addFieldToFilter('user_id', 0)
            ->addFieldToFilter('user_type', UserContextInterface::USER_TYPE_ADMIN)
            ->addFieldToFilter('role_name', 'Administrators');

        if ($roleCollection->count() == 0) {
            $admGroupRole = $this->authFactory->createRole()->setData(
                [
                    'parent_id' => 0,
                    'tree_level' => 1,
                    'sort_order' => 1,
                    'role_type' => RoleGroup::ROLE_TYPE,
                    'user_id' => 0,
                    'user_type' => UserContextInterface::USER_TYPE_ADMIN,
                    'role_name' => 'Administrators',
                ]
            )->save();
        } else {
            foreach ($roleCollection as $item) {
                $admGroupRole = $item;
                break;
            }
        }
        $rulesCollection = $this->authFactory->createRulesCollection()
            ->addFieldToFilter('role_id', $admGroupRole->getId())
            ->addFieldToFilter('resource_id', 'all');
        if ($rulesCollection->count() == 0) {
            $this->authFactory->createRules()->setData(
                [
                    'role_id' => $admGroupRole->getId(),
                    'resource_id' => 'Magento_Backend::all',
                    'privileges' => null,
                    'permission' => 'allow',
                ]
            )->save();
        } else {
            /** @var \Magento\Authorization\Model\Rules $rule */
            foreach ($rulesCollection as $rule) {
                $rule->setData('resource_id', 'Magento_Backend::all')->save();
            }
        }
        /**
         * Delete rows by condition from authorization_rule
         */
        $setup->startSetup();
        $tableName = $setup->getTable('authorization_rule');
        if ($tableName) {
            $setup->getConnection()->delete($tableName, ['resource_id = ?' => 'admin/system/tools/compiler']);
        }
        $setup->endSetup();

    }

}
