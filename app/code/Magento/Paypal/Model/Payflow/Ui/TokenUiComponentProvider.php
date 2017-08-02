<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Ui;

use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;

/**
 * Class TokenUiComponentProvider
 * @since 2.1.0
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var TokenUiComponentInterfaceFactory
     * @since 2.1.0
     */
    private $componentFactory;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @since 2.1.0
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory
    ) {
        $this->componentFactory = $componentFactory;
    }

    /**
     * Get UI component for token
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     * @since 2.1.0
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $jsonDetails = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => Transparent::CC_VAULT_CODE,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash()
                ],
                'name' => 'Magento_Paypal/js/view/payment/method-renderer/payflowpro/vault'
            ]
        );

        return $component;
    }
}
