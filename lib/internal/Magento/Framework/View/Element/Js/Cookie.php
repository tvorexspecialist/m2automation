<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Js;

use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @api
 * @since 2.0.0
 */
class Cookie extends Template
{
    /**
     * Session config
     *
     * @var ConfigInterface
     * @since 2.0.0
     */
    protected $sessionConfig;

    /**
     * @var \Magento\Framework\Validator\Ip
     * @since 2.0.0
     */
    protected $ipValidator;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigInterface $cookieConfig
     * @param \Magento\Framework\Validator\Ip $ipValidator
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        ConfigInterface $cookieConfig,
        \Magento\Framework\Validator\Ip $ipValidator,
        array $data = []
    ) {
        $this->sessionConfig = $cookieConfig;
        $this->ipValidator = $ipValidator;
        parent::__construct($context, $data);
    }

    /**
     * Get configured cookie domain
     *
     * @return string
     * @since 2.0.0
     */
    public function getDomain()
    {
        $domain = $this->sessionConfig->getCookieDomain();

        if ($this->ipValidator->isValid($domain)) {
            return $domain;
        }

        if (!empty($domain[0]) && $domain[0] !== '.') {
            $domain = '.' . $domain;
        }
        return $domain;
    }

    /**
     * Get configured cookie path
     *
     * @return string
     * @since 2.0.0
     */
    public function getPath()
    {
        return $this->sessionConfig->getCookiePath();
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getLifetime()
    {
        return $this->sessionConfig->getCookieLifetime();
    }
}
