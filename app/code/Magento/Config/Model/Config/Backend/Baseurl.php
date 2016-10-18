<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Backend;

class Baseurl extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\View\Asset\MergeService
     */
    protected $_mergeService;

    /**
     * @var array
     */
    private $allowedSchemes = ['http', 'https'];

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\View\Asset\MergeService $mergeService
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\View\Asset\MergeService $mergeService,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_mergeService = $mergeService;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate a base URL field value
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        try {
            if (!$this->_validateUnsecure($value) && !$this->_validateSecure($value)) {
                $this->_validateFullyQualifiedUrl($value);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $field = $this->getFieldConfig();
            $label = $field && is_array($field) ? $field['label'] : 'value';
            $msg = __('Invalid %1. %2', $label, $e->getMessage());
            $error = new \Magento\Framework\Exception\LocalizedException($msg, $e);
            throw $error;
        }
    }

    /**
     * Validation sub-routine for unsecure base URLs
     *
     * @param string $value
     * @return bool
     */
    private function _validateUnsecure($value)
    {
        $placeholders = ['{{unsecure_base_url}}'];
        switch ($this->getPath()) {
            case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL:
                $this->_assertValuesOrUrl(['{{base_url}}'], $value);
                break;
            case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_LINK_URL:
                $this->_assertStartsWithValuesOrUrl($placeholders, $value);
                break;
            case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_STATIC_URL:
            case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL:
                $this->_assertStartsWithValuesOrUrlOrEmpty($placeholders, $value);
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * Validation sub-routine for secure base URLs
     *
     * @param string $value
     * @return bool
     */
    private function _validateSecure($value)
    {
        $placeholders = ['{{unsecure_base_url}}', '{{secure_base_url}}'];
        switch ($this->getPath()) {
            case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL:
                $this->_assertValuesOrUrl(['{{base_url}}', '{{unsecure_base_url}}'], $value);
                break;
            case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL:
                $this->_assertStartsWithValuesOrUrl($placeholders, $value);
                break;
            case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL:
            case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL:
                $this->_assertStartsWithValuesOrUrlOrEmpty($placeholders, $value);
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * Value equals to one of provided items or is a URL
     *
     * @param array $values
     * @param string $value
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _assertValuesOrUrl(array $values, $value)
    {
        if (!in_array($value, $values) && !$this->_isFullyQualifiedUrl($value)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Value must be a URL or one of placeholders: %1', implode(',', $values))
            );
        }
    }

    /**
     * Value starts with one of provided items or is a URL
     *
     * @param array $values
     * @param string $value
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _assertStartsWithValuesOrUrl(array $values, $value)
    {
        $quoted = array_map('preg_quote', $values, array_fill(0, count($values), '/'));
        if (!preg_match('/^(' . implode('|', $quoted) . ')(.+\/)?$/', $value) && !$this->_isFullyQualifiedUrl($value)
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Specify a URL or path that starts with placeholder(s): %1, and ends with "/".',
                    implode(', ', $values)
                )
            );
        }
    }

    /**
     * Value starts with, empty or is a URL
     *
     * @param array $values
     * @param string $value
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _assertStartsWithValuesOrUrlOrEmpty(array $values, $value)
    {
        if (empty($value)) {
            return;
        }
        try {
            $this->_assertStartsWithValuesOrUrl($values, $value);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $msg = __('%1 An empty value is allowed as well.', $e->getMessage());
            $error = new \Magento\Framework\Exception\LocalizedException($msg, $e);
            throw $error;
        }
    }

    /**
     * Default validation of a URL
     *
     * @param string $value
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _validateFullyQualifiedUrl($value)
    {
        if (!$this->_isFullyQualifiedUrl($value)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Specify a fully qualified URL.'));
        }
    }

    /**
     * Check that URL contains allowed symbols
     *
     * @param string $value
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function validateUrl($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Domain Name can contain only letters, digits and hyphen.')
            );
        }
    }

    /**
     * Check that scheme there is in list of allowed schemes
     *
     * @param string $value
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function validateSchemes($value)
    {
        if (!in_array($value, $this->allowedSchemes)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can use only following schemes: %1', implode(', ', $this->allowedSchemes))
            );
        }
    }

    /**
     * Whether the provided value can be considered as a fully qualified URL
     *
     * @param string $value
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _isFullyQualifiedUrl($value)
    {
        $url = parse_url($value);

        $result = isset($url['scheme']) && isset($url['host']) && preg_match('/\/$/', $value);
        if ($result) {
            $this->validateSchemes($url['scheme']);
            $this->validateUrl($value);
        }

        return $result;
    }

    /**
     * Clean compiled JS/CSS when updating url configuration settings
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            switch ($this->getPath()) {
                case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL:
                case \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL:
                case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL:
                case \Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL:
                    $this->_mergeService->cleanMergedJsCss();
                    break;
            }
        }
        return parent::afterSave();
    }
}
