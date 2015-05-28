<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ScopeInterface;

/**
 * Template model class
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractTemplate extends AbstractModel implements TemplateTypesInterface
{
    /**
     * Default design area for emulation
     */
    const DEFAULT_DESIGN_AREA = 'frontend';

    /**
     * Email logo url
     *
     * @var string
     */
    const XML_PATH_DESIGN_EMAIL_LOGO = 'design/email/logo';

    /**
     * Email logo alt text
     *
     * @var string
     */
    const XML_PATH_DESIGN_EMAIL_LOGO_ALT = 'design/email/logo_alt';

    /**
     * Email logo width
     *
     * @var string
     */
    const XML_PATH_DESIGN_EMAIL_LOGO_WIDTH = 'design/email/logo_width';

    /**
     * Email logo height
     *
     * @var string
     */
    const XML_PATH_DESIGN_EMAIL_LOGO_HEIGHT = 'design/email/logo_height';

    /**
     * Configuration of design package for template
     *
     * @var \Magento\Framework\Object
     */
    protected $_designConfig;

    /**
     * List of CSS files that should be inlined on this template
     *
     * @var array
     */
    protected $_inlineCssFiles = array();

    /**
     * Whether template is child of another template
     *
     * @var bool
     */
    protected $_isChildTemplate = false;

    /**
     * Configuration of emulated design package.
     *
     * @var \Magento\Framework\Object|boolean
     */
    protected $_emulatedDesignConfig = false;

    /**
     * Package area
     *
     * @var string
     */
    protected $_area;

    /**
     * Store id
     *
     * @var int
     */
    protected $_store;

    /**
     * @var null|\Magento\Email\Model\AbstractTemplate
     */
    protected $_templateFactory = null;

    /**
     * Design package instance
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design = null;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    protected $_emailConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Template\Config $emailConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    ) {
        $this->_design = $design;
        $this->_area = isset($data['area']) ? $data['area'] : null;
        $this->_store = isset($data['store']) ? $data['store'] : null;
        $this->_appEmulation = $appEmulation;
        $this->_storeManager = $storeManager;
        $this->_assetRepo = $assetRepo;
        $this->_filesystem = $filesystem;
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->_emailConfig = $emailConfig;
        parent::__construct($context, $registry, null, null, $data);
    }

    /**
     * Get contents of the included template for template directive
     *
     * @param string $configPath
     * @param array $variables
     * @return string
     */
    public function getTemplateContent($configPath, array $variables)
    {
        $template = $this->_getTemplateInstance();
        $template->loadByConfigPath($configPath, $variables);

        // Ensure child templates have the same area/store context as parent
        $template->setDesignConfig($this->getDesignConfig()->toArray());

        // Indicate that this is a child template so that when the template is being filtered, directives such as
        // inlinecss can respond accordingly
        $template->setIsChildTemplate(true);

        return $template->getProcessedTemplate($variables);
    }

    /**
     * Load template by XML configuration path. Loads template from database if it exists and has been overridden in
     * configuration. Otherwise loads from the filesystem.
     *
     * @param string $configPath
     * @return \Magento\Email\Model\AbstractTemplate
     */
    public function loadByConfigPath($configPath)
    {
        $templateId = $this->_scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $this->getDesignConfig()->getStore()
        );

        if (is_numeric($templateId)) {
            // Template was overridden in backend, so load template from database
            $this->load($templateId);
        } else {
            // Load from filesystem
            $this->loadDefault($templateId);
        }

        // Templates loaded via the {{template config_path=""}} syntax don't support the subject/vars/styles
        // comment blocks, so strip them out
        $templateText = preg_replace('/<!--@(\w+)\s*(.*?)\s*@-->/us', '', $this->getTemplateText());
        // Remove comment lines and extra spaces
        $templateText = trim(preg_replace('#\{\*.*\*\}#suU', '', $templateText));

        $this->setTemplateText($templateText);

        return $this;
    }

    /**
     * Load default email template
     *
     * @param string $templateId
     * @return $this
     */
    public function loadDefault($templateId)
    {
        $templateFile = $this->_emailConfig->getTemplateFilename($templateId);
        $templateType = $this->_emailConfig->getTemplateType($templateId);
        $templateTypeCode = $templateType == 'html' ? self::TYPE_HTML : self::TYPE_TEXT;
        $this->setTemplateType($templateTypeCode);

        $modulesDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MODULES);
        $templateText = $modulesDirectory->readFile($modulesDirectory->getRelativePath($templateFile));

        /**
         * trim copyright message for text templates
         */
        if ('html' != $templateType
            && preg_match('/^<!--[\w\W]+?-->/m', $templateText, $matches)
            && strpos($matches[0], 'Copyright') > 0
        ) {
            $templateText = str_replace($matches[0], '', $templateText);
        }

        if (preg_match('/<!--@subject\s*(.*?)\s*@-->/u', $templateText, $matches)) {
            $this->setTemplateSubject($matches[1]);
            $templateText = str_replace($matches[0], '', $templateText);
        }

        if (preg_match('/<!--@vars\s*((?:.)*?)\s*@-->/us', $templateText, $matches)) {
            $this->setData('orig_template_variables', str_replace("\n", '', $matches[1]));
            $templateText = str_replace($matches[0], '', $templateText);
        }

        if (preg_match('/<!--@styles\s*(.*?)\s*@-->/s', $templateText, $matches)) {
            $this->setTemplateStyles($matches[1]);
            $templateText = str_replace($matches[0], '', $templateText);
        }

        // Remove comment lines and extra spaces
        $templateText = trim(preg_replace('#\{\*.*\*\}#suU', '', $templateText));

        $this->setTemplateText($templateText);
        $this->setId($templateId);

        return $this;
    }

    /**
     * Add filename of CSS file to inline
     *
     * @param string $file
     * @return $this
     */
    public function addInlineCssFile($file)
    {
        $this->_inlineCssFiles[] = $file;
        return $this;
    }

    /**
     * Get filename of CSS file to inline
     *
     * @return array
     */
    public function getInlineCssFiles()
    {
        return $this->_inlineCssFiles;
    }

    /**
     * Merge HTML and CSS and returns HTML that has CSS styles applied "inline" to the HTML tags. This is necessary
     * in order to support all email clients.
     *
     * @param $html
     * @return string
     */
    protected function _applyInlineCss($html)
    {
        // Check to see if the {{inlinecss file=""}} directive set CSS file(s) to inline
        $cssToInline = $this->_getCssFilesContent(
            $this->getInlineCssFiles()
        );
        // Only run Emogrify if HTML exists and if there is at least one file to inline
        if ($html && !empty($cssToInline)) {
            try {
                $emogrifier = new \Pelago\Emogrifier();
                $emogrifier->setHtml($html);
                $emogrifier->setCss($cssToInline);

                // Don't parse inline <style> tags, since existing tag is intentionally for non-inline styles
                $emogrifier->disableStyleBlocksParsing();

                $processedHtml = $emogrifier->emogrify();
            } catch (Exception $e) {
                if ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
                    $processedHtml = sprintf(__('{CSS inlining error: %s}'), $e->getMessage())
                        . PHP_EOL
                        . $html;
                } else {
                    $processedHtml = $html;
                }
                $this->_logger->error($e);
            }
        } else {
            $processedHtml = $html;
        }
        return $processedHtml;
    }

    /**
     * Loads CSS file from materialized static view directory
     *
     * @param $file
     * @return string
     */
    public function getCssFileContent($file)
    {
        $designParams = $this->getDesignParams();

        $asset = $this->_assetRepo->createAsset($file, $designParams);
        return $asset->getContent();
    }

    /**
     * Loads CSS content from filesystem.
     *
     * @param array $files
     * @return string
     */
    protected function _getCssFilesContent($files)
    {
        // Remove duplicate files
        $files = array_unique($files);

        $css = '';
        foreach ($files as $file) {
            $css .= $this->getCssFileContent($file);
        }
        return $css;
    }

    /**
     * Get default email logo image
     *
     * @return string
     */
    public function getDefaultEmailLogo()
    {
        $designParams = $this->getDesignParams();
        return $this->_assetRepo->getUrlWithParams(
            'Magento_Email::logo_email.png',
            $designParams
        );
    }

    /**
     * Return logo URL for emails. Take logo from theme if custom logo is undefined
     *
     * @param  \Magento\Store\Model\Store|int|string $store
     * @return string
     */
    protected function _getLogoUrl($store)
    {
        $store = $this->_storeManager->getStore($store);
        $fileName = $this->_scopeConfig->getValue(
            self::XML_PATH_DESIGN_EMAIL_LOGO,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($fileName) {
            $uploadDir = \Magento\Config\Model\Config\Backend\Email\Logo::UPLOAD_DIR;
            $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            if ($mediaDirectory->isFile($uploadDir . '/' . $fileName)) {
                return $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $uploadDir . '/' . $fileName;
            }
        }
        return $this->getDefaultEmailLogo();
    }

    /**
     * Return logo alt for emails
     *
     * @param  \Magento\Store\Model\Store|int|string $store
     * @return string
     */
    protected function _getLogoAlt($store)
    {
        $store = $this->_storeManager->getStore($store);
        $alt = $this->_scopeConfig->getValue(
            self::XML_PATH_DESIGN_EMAIL_LOGO_ALT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($alt) {
            return $alt;
        }
        return $store->getFrontendName();
    }

    /**
     * Add variables that are used by transactional and newsletter emails
     *
     * @param $variables
     * @param $storeId
     * @return mixed
     */
    protected function _addEmailVariables($variables, $storeId)
    {
        $store = $this->_storeManager->getStore($storeId);
        if (!isset($variables['store'])) {
            $variables['store'] = $store;
        }
        if (!isset($variables['logo_url'])) {
            $variables['logo_url'] = $this->_getLogoUrl($storeId);
        }
        if (!isset($variables['logo_alt'])) {
            $variables['logo_alt'] = $this->_getLogoAlt($storeId);
        }
        if (!isset($variables['logo_width'])) {
            $variables['logo_width'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DESIGN_EMAIL_LOGO_WIDTH,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        if (!isset($variables['logo_height'])) {
            $variables['logo_height'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DESIGN_EMAIL_LOGO_HEIGHT,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        if (!isset($variables['store_phone'])) {
            $variables['store_phone'] = $this->_scopeConfig->getValue(
                \Magento\Store\Model\Store::XML_PATH_STORE_STORE_PHONE,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        if (!isset($variables['store_hours'])) {
            $variables['store_hours'] = $this->_scopeConfig->getValue(
                \Magento\Store\Model\Store::XML_PATH_STORE_STORE_HOURS,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        if (!isset($variables['store_email'])) {
            $variables['store_email'] = $this->_scopeConfig->getValue(
                'trans_email/ident_support/email',
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        // If template is text mode, don't include styles
        if (!$this->isPlain() && !isset($variables['template_styles'])) {
            $variables['template_styles'] = $this->getTemplateStyles();
        }

        return $variables;
    }

    /**
     * Apply design config so that emails are processed within the context of the appropriate area/store/theme
     *
     * @return $this
     */
    protected function _applyDesignConfig()
    {
        $designConfig = $this->getDesignConfig();
        $store = $designConfig->getStore();
        $storeId = is_object($store) ? $store->getId() : $store;
        $area = $designConfig->getArea();
        if ($storeId !== null) {
            $this->_appEmulation->startEnvironmentEmulation(
                $storeId,
                $area,
                // Force emulation in case email is being sent from same store so that theme will be loaded. Helpful
                // for situations where emails may be sent from bootstrap files that load frontend store, but not theme
                true
            );
        }
        return $this;
    }

    /**
     * Revert design settings to previous
     *
     * @return $this
     */
    protected function _cancelDesignConfig()
    {
        $this->_appEmulation->stopEnvironmentEmulation();
        return $this;
    }

    /**
     * Returns the design params for the template being processed
     *
     * @return array
     */
    public function getDesignParams()
    {
        $designParams = array(
            // Retrieve area from getDesignConfig, rather than the getDesignTheme->getArea(), as the latter doesn't
            // return the emulated area
            'area' => $this->getDesignConfig()->getArea(),
            'theme' => $this->_design->getDesignTheme()->getCode(),
            'locale' => $this->_design->getLocale(),
        );
        return $designParams;
    }

    /**
     * Get design configuration data
     *
     * @return \Magento\Framework\Object
     */
    public function getDesignConfig()
    {
        if ($this->_designConfig === null) {
            if ($this->_area === null) {
                $this->_area = $this->_design->getArea();
            }
            if ($this->_store === null) {
                $this->_store = $this->_storeManager->getStore()->getId();
            }
            $this->_designConfig = new \Magento\Framework\Object(
                ['area' => $this->_area, 'store' => $this->_store]
            );
        }
        return $this->_designConfig;
    }

    /**
     * Initialize design information for template processing
     *
     * @param array $config
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setDesignConfig(array $config)
    {
        if (!isset($config['area']) || !isset($config['store'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Design config must have area and store.'));
        }
        $this->getDesignConfig()->setData($config);
        return $this;
    }

    /**
     * Gets whether template is child of another template
     *
     * @return bool
     */
    public function getIsChildTemplate()
    {
        return $this->_isChildTemplate;
    }

    /**
     * Sets whether template is child of another template
     *
     * @param bool $isChildTemplate
     * @return $this
     */
    public function setIsChildTemplate($isChildTemplate)
    {
        $this->_isChildTemplate = (bool) $isChildTemplate;
        return $this;
    }

    /**
     * Save current design config and replace with design config from specified store
     * Event is not dispatched.
     *
     * @param int|string $storeId
     * @param string $area
     * @return void
     */
    public function emulateDesign($storeId, $area = self::DEFAULT_DESIGN_AREA)
    {
        if ($storeId) {
            // save current design settings
            $this->_emulatedDesignConfig = clone $this->getDesignConfig();
            if (
                $this->getDesignConfig()->getStore() != $storeId
                || $this->getDesignConfig()->getArea() != $area
            ) {
                $this->setDesignConfig(['area' => $area, 'store' => $storeId]);
                $this->_applyDesignConfig();
            }
        } else {
            $this->_emulatedDesignConfig = false;
        }
    }

    /**
     * Revert to last design config, used before emulation
     *
     * @return void
     */
    public function revertDesign()
    {
        if ($this->_emulatedDesignConfig) {
            $this->setDesignConfig($this->_emulatedDesignConfig->getData());
            $this->_cancelDesignConfig();
            $this->_emulatedDesignConfig = false;
        }
    }

    /**
     * Return true if template type eq text
     *
     * @return boolean
     */
    public function isPlain()
    {
        return $this->getType() == self::TYPE_TEXT;
    }

    /**
     * If class has set a template factory, return a new object. Else throw an exception.
     * This allows child classes like \Magento\Email\Model\Template and \Magento\Newsletter\Model\Template to set
     * their own factory objects.
     *
     * @return \Magento\Email\Model\AbstractTemplate
     * @throws \UnexpectedValueException
     */
    protected function _getTemplateInstance()
    {
        if (!$this->_templateFactory) {
            throw new \UnexpectedValueException('_templateFactory must be set');
        }
        return $this->_templateFactory->create([
            // Pass filesystem object to child template. Intended to be used for the test isolation purposes.
            'filesystem' => $this->_filesystem
        ]);
    }

    /**
     * Getter for template type
     *
     * @return int|string
     */
    abstract public function getType();
}
