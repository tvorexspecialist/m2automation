<?php
/**
 * High-level interface for email templates data that hides format from the client code
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template;

use Magento\Framework\App\Filesystem\DirectoryList;

class Config implements \Magento\Framework\Mail\Template\ConfigInterface
{
    /**
     * @var \Magento\Email\Model\Template\Config\Data
     */
    protected $_dataStorage;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * Themes directory
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $themesDirectory;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @param \Magento\Email\Model\Template\Config\Data $dataStorage
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     */
    public function __construct(
        \Magento\Email\Model\Template\Config\Data $dataStorage,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\View\FileSystem $viewFileSystem
    ) {
        $this->_dataStorage = $dataStorage;
        $this->_moduleReader = $moduleReader;
        $this->themesDirectory = $fileSystem->getDirectoryRead(DirectoryList::THEMES);
        $this->viewFileSystem = $viewFileSystem;
    }

    /**
     * Return list of all email templates, both default module and theme-specific templates
     *
     * @return array[]
     */
    public function getAvailableTemplates()
    {
        $templates = [];
        foreach (array_keys($this->_dataStorage->get()) as $templateId) {
            $templates[] = [
                'value' => $templateId,
                'label' => $this->getTemplateLabel($templateId),
                'group' => $this->getTemplateModule($templateId),
            ];
            $themeTemplates = $this->getThemeTemplates($templateId);
            $templates = array_merge($templates, $themeTemplates);
        }
        return $templates;
    }

    /**
     * Find all theme-based email templates for a given template ID
     *
     * @param string $templateId
     * @return array[]
     */
    public function getThemeTemplates($templateId)
    {
        $templates = [];

        $area = $this->getTemplateArea($templateId);
        $themePath = '*/*';
        $module = $this->getTemplateModule($templateId);
        $filename = $this->_getInfo($templateId, 'file');
        $searchPattern = "{$area}/{$themePath}/{$module}/email/{$filename}";
        $files = $this->themesDirectory->search($searchPattern);

        $pattern = "#^(?<area>[^/]+)/(?<themeVendor>[^/]+)/(?<themeName>[^/]+)/#i";
        foreach ($files as $file) {
            if (!preg_match($pattern, $file, $matches)) {
                continue;
            }
            $themeVendor = $matches['themeVendor'];
            $themeName = $matches['themeName'];

            $templates[] = [
                'value' => sprintf(
                    '%s/%s/%s',
                    $templateId,
                    $themeVendor,
                    $themeName
                ),
                'label' => sprintf(
                    '%s (%s/%s)',
                    $this->getTemplateLabel($templateId),
                    $themeVendor,
                    $themeName
                ),
                'group' => $this->getTemplateModule($templateId),
            ];
        }
        return $templates;
    }

    /**
     * Parses a template ID and returns an array of templateId and theme
     *
     * @param string $templateId
     * @return array an array of array('templateId' => '...', 'theme' => '...')
     */
    public function parseTemplateIdParts($templateId)
    {
        $parts = [
            'templateId' => $templateId,
            'theme' => null
        ];
        $pattern = "#^(?<templateId>[^/]+)/(?<themeVendor>[^/]+)/(?<themeName>[^/]+)#i";
        if (preg_match($pattern, $templateId, $matches)) {
            $parts['templateId'] = $matches['templateId'];
            $parts['theme'] = $matches['themeVendor'] . '/' . $matches['themeName'];
        }
        return $parts;
    }

    /**
     * Retrieve translated label of an email template
     *
     * @param string $templateId
     * @return \Magento\Framework\Phrase
     */
    public function getTemplateLabel($templateId)
    {
        return __($this->_getInfo($templateId, 'label'));
    }

    /**
     * Retrieve type of an email template
     *
     * @param string $templateId
     * @return string
     */
    public function getTemplateType($templateId)
    {
        return $this->_getInfo($templateId, 'type');
    }

    /**
     * Retrieve fully-qualified name of a module an email template belongs to
     *
     * @param string $templateId
     * @return string
     */
    public function getTemplateModule($templateId)
    {
        return $this->_getInfo($templateId, 'module');
    }

    /**
     * Retrieve the area an email template belongs to
     *
     * @param string $templateId
     * @return string
     */
    public function getTemplateArea($templateId)
    {
        return $this->_getInfo($templateId, 'area');
    }

    /**
     * Retrieve full path to an email template file
     *
     * @param string $templateId
     * @param array|null $designParams
     * @return string
     */
    public function getTemplateFilename($templateId, $designParams = [])
    {
        // If design params aren't passed, then use area/module defined in email_templates.xml
        if (!isset($designParams['area'])) {
            $designParams['area'] = $this->getTemplateArea($templateId);
        }
        $module = $this->getTemplateModule($templateId);
        $designParams['module'] = $module;

        $file = $this->_getInfo($templateId, 'file');

        return $this->viewFileSystem->getEmailTemplateFileName($file, $designParams, $module);
    }

    /**
     * Retrieve value of a field of an email template
     *
     * @param string $templateId Name of an email template
     * @param string $fieldName Name of a field value of which to return
     * @return string
     * @throws \UnexpectedValueException
     */
    protected function _getInfo($templateId, $fieldName)
    {
        $data = $this->_dataStorage->get();
        if (!isset($data[$templateId])) {
            throw new \UnexpectedValueException("Email template '{$templateId}' is not defined.");
        }
        if (!isset($data[$templateId][$fieldName])) {
            throw new \UnexpectedValueException(
                "Field '{$fieldName}' is not defined for email template '{$templateId}'."
            );
        }
        return $data[$templateId][$fieldName];
    }
}
