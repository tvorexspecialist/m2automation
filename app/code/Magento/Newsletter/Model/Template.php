<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

/**
 * Template model
 *
 * @method \Magento\Newsletter\Model\Resource\Template _getResource()
 * @method \Magento\Newsletter\Model\Resource\Template getResource()
 * @method string getTemplateCode()
 * @method \Magento\Newsletter\Model\Template setTemplateCode(string $value)
 * @method \Magento\Newsletter\Model\Template setTemplateText(string $value)
 * @method \Magento\Newsletter\Model\Template setTemplateTextPreprocessed(string $value)
 * @method string getTemplateStyles()
 * @method \Magento\Newsletter\Model\Template setTemplateStyles(string $value)
 * @method int getTemplateType()
 * @method \Magento\Newsletter\Model\Template setTemplateType(int $value)
 * @method string getTemplateSubject()
 * @method \Magento\Newsletter\Model\Template setTemplateSubject(string $value)
 * @method string getTemplateSenderName()
 * @method \Magento\Newsletter\Model\Template setTemplateSenderName(string $value)
 * @method string getTemplateSenderEmail()
 * @method \Magento\Newsletter\Model\Template setTemplateSenderEmail(string $value)
 * @method int getTemplateActual()
 * @method \Magento\Newsletter\Model\Template setTemplateActual(int $value)
 * @method string getAddedAt()
 * @method \Magento\Newsletter\Model\Template setAddedAt(string $value)
 * @method string getModifiedAt()
 * @method \Magento\Newsletter\Model\Template setModifiedAt(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Template extends \Magento\Email\Model\AbstractTemplate
{
    /**
     * Mail object
     *
     * @var \Zend_Mail
     */
    protected $_mail;

    /**
     * Store manager to emulate design
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Http-request, used to determine current store in multi-store mode
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Filter factory
     *
     * @var \Magento\Newsletter\Model\Template\FilterFactory
     */
    protected $_filterFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param \Magento\Email\Model\TemplateFactory $templateFactory The template directive requires an email
     *        template model, not newsletter model, as templates overridden in backend are loaded from email table.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Newsletter\Model\Template\FilterFactory $filterFactory,
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        \Magento\Email\Model\Template\Config $emailConfig,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Newsletter\Model\Template\FilterFactory $filterFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $design,
            $registry,
            $appEmulation,
            $storeManager,
            $assetRepo,
            $filesystem,
            $scopeConfig,
            $emailConfig,
            $templateFactory,
            $data
        );
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_filterFactory = $filterFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Newsletter\Model\Resource\Template');
    }

    /**
     * Validate Newsletter template
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        $validators = [
            'template_code' => [\Zend_Filter_Input::ALLOW_EMPTY => false],
            'template_type' => 'Int',
            'template_sender_email' => 'EmailAddress',
            'template_sender_name' => [\Zend_Filter_Input::ALLOW_EMPTY => false],
        ];
        $data = [];
        foreach (array_keys($validators) as $validateField) {
            $data[$validateField] = $this->getDataUsingMethod($validateField);
        }

        $validateInput = new \Zend_Filter_Input([], $validators, $data);
        if (!$validateInput->isValid()) {
            $errorMessages = [];
            foreach ($validateInput->getMessages() as $messages) {
                if (is_array($messages)) {
                    foreach ($messages as $message) {
                        $errorMessages[] = $message;
                    }
                } else {
                    $errorMessages[] = $messages;
                }
            }

            throw new \Magento\Framework\Exception\LocalizedException(__(join("\n", $errorMessages)));
        }
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        $this->validate();
        return parent::beforeSave();
    }

    /**
     * Getter for template type
     *
     * @return int|string
     */
    public function getType()
    {
        return $this->getTemplateType();
    }

    /**
     * Retrieve processed template subject
     *
     * @param array $variables
     * @return string
     */
    public function getProcessedTemplateSubject(array $variables)
    {
        $variables['this'] = $this;

        return $this->getTemplateFilter()
            ->setVariables($variables)
            ->filter($this->getTemplateSubject());
    }

    /**
     * Retrieve template text wrapper
     *
     * @return string
     */
    public function getTemplateText()
    {
        if (!$this->getData('template_text') && !$this->getId()) {
            $this->setData(
                'template_text',
                __(
                    'Follow this link to unsubscribe <!-- This tag is for unsubscribe link  -->' .
                    '<a href="{{var subscriber.getUnsubscriptionLink()}}">{{var subscriber.getUnsubscriptionLink()}}' .
                    '</a>'
                )
            );
        }

        return $this->getData('template_text');
    }

    /**
     * @return \Magento\Newsletter\Model\Template\FilterFactory
     */
    protected function getFilterFactory()
    {
        return $this->_filterFactory;
    }

    /**
     * Check if template can be added to newsletter queue
     *
     * @return boolean
     */
    public function isValidForSend()
    {
        return !$this->scopeConfig->isSetFlag(
            \Magento\Email\Model\Template::XML_PATH_SYSTEM_SMTP_DISABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && $this->getTemplateSenderName() && $this->getTemplateSenderEmail() && $this->getTemplateSubject();
    }
}
