<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\GraphQl\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\GraphQl\ResolverContextInterface;

/**
 * Concrete implementation for @see ResolverContextInterface
 *
 * The purpose for this that GraphQL specification wants to make use of such object where multiple modules can
 * participate with data through extension attributes.
 */
class ResolverContext extends \Magento\Framework\Model\AbstractExtensibleModel implements ResolverContextInterface
{
    /**#@+
     * Constants defined for type of context
     */
    const USER_TYPE_ID  = 'user_type';
    const USER_ID = 'user_id';
    /**#@-*/

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param UserContextInterface|null $userContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        UserContextInterface $userContext,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory
        );
        if (isset($data['id'])) {
            $this->setId($data['id']);
        }
        if (isset($data['type'])) {
            $this->setId($data['type']);
        }
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\GraphQl\ResolverContextExtensionInterface||null
     */
    public function getExtensionAttributes() : ?\Magento\GraphQl\Model\ResolverContextExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Framework\GraphQl\ResolverContextExtensionInterface $extensionAttributes
     * @return ResolverContextInterface
     */
    public function setExtensionAttributes(
        \Magento\Framework\GraphQl\ResolverContextExtensionInterface $extensionAttributes
    ) : ResolverContextInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @inheritDoc
     */
    public function getUserId() : int
    {
        if (!$this->getData(self::USER_ID)) {
            $this->setUserId((int) $this->userContext->getUserId());
        }
        return (int) $this->getData(self::USER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setUserId(int $userId) : ResolverContextInterface
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * @inheritDoc
     */
    public function getUserType() : int
    {
        if (!$this->getData(self::USER_TYPE_ID)) {
            $this->setUserType($this->userContext->getUserType());
        }
        return (int) $this->getData(self::USER_TYPE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setUserType(int $typeId) : ResolverContextInterface
    {
        return $this->setData(self::USER_TYPE_ID, $typeId);
    }
}
