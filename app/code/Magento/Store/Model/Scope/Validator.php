<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\Scope;

use InvalidArgumentException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\App\ScopeResolverPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Validator validates scope and scope code.
 */
class Validator implements ValidatorInterface
{
    /**
     * @var ScopeResolverPool
     */
    protected $scopeResolverPool;

    /**
     * @param ScopeResolverPool $scopeResolverPool
     */
    public function __construct(ScopeResolverPool $scopeResolverPool)
    {
        $this->scopeResolverPool = $scopeResolverPool;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($scope, $scopeCode = null)
    {
        if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT && empty($scopeCode)) {
            return true;
        }

        if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT && !empty($scopeCode)) {
            throw new LocalizedException(__(
                'Scope code shouldn\'t be passed for scope "%1"',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            ));
        }

        if (empty($scope)) {
            throw new LocalizedException(__('Scope can\'t be empty'));
        }

        if (empty($scopeCode)) {
            throw new LocalizedException(__('Scope code can\'t be empty'));
        }

        if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $scopeCode)) {
            throw new LocalizedException(__('Wrong scope code format'));
        }

        try {
            $scopeResolver = $this->scopeResolverPool->get($scope);
        } catch (InvalidArgumentException $e) {
            throw new LocalizedException(__('Scope "%1" doesn\'t exist', $scope));
        }

        try {
            $scopeResolver->getScope($scopeCode)->getId();
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Scope code "%1" doesn\'t exist in scope "%2"', $scopeCode, $scope));
        }

        return true;
    }
}
