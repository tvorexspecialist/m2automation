<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Block\Adminhtml\Product\Edit;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class NewVideo extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->setUseContainer(true);
    }

    /**
     * Form preparation
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'new_video_form',
                'class' => 'admin__scope-old',
                'enctype' => 'multipart/form-data',
            ]
        ]);
        $form->setUseContainer($this->getUseContainer());

        $form->addField('new_video_messages', 'note', []);

        $fieldset = $form->addFieldset('new_video_form_fieldset', []);

        $fieldset->addField(
            '',
            'hidden',
            [
                'name' => 'form_key',
                'value' => $this->getFormKey(),
            ]
        );

        $fieldset->addField(
            'item_id',
            'hidden',
            []
        );

        $fieldset->addField(
            'file_name',
            'hidden',
            []
        );

        $fieldset->addField(
            'video_provider',
            'hidden',
            [
                'name' => 'video_provider',
            ]
        );

        $fieldset->addField(
            'video_url',
            'text',
            [
                'class' => 'edited-data validate-url',
                'label' => __('Url'),
                'title' => __('Url'),
                'required' => true,
                'name' => 'video_url',
                'note' => 'Youtube or Vimeo supported',
            ]
        );


        $fieldset->addField(
            'video_title',
            'text',
            [
                'class' => 'edited-data',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'name' => 'video_title',
            ]
        );

        $fieldset->addField(
            'video_description',
            'textarea',
            [
                'class' => 'edited-data',
                'label' => __('Description'),
                'title' => __('Description'),
                'name' => 'video_description',
            ]
        );

        $fieldset->addField(
            'new_video_screenshot',
            'file',
            [
                'label' => __('Preview Image'),
                'title' => __('Preview Image'),
                'name' => 'image',
            ]
        );

        $fieldset->addField(
            'new_video_screenshot_preview',
            'button',
            [
                'class' => 'preview_hidden_image_input_button',
                'label' => '',
                'name' => '_preview',
            ]
        );


        $fieldset->addField(
            'new_video_get',
            'button',
            [
                'label' => '',
                'title' => __('Get Video Information'),
                'name' => 'new_video_get',
                'value' => 'Get Video Information',
            ]
        );

        $fieldset->addField(
            'video_base_image',
            'checkbox',
            [
                'class' => 'video_image_role',
                'label' => 'Base Image',
                'title' => __('Base Image'),
                'data-role' => 'role-type-selector',
                'value' => 'image',
            ]
        );

        $fieldset->addField(
            'video_small_image',
            'checkbox',
            [
                'class' => 'video_image_role',
                'label' => 'Small Image',
                'title' => __('Small Image'),
                'data-role' => 'role-type-selector',
                'value' => 'small_image',
            ]
        );

        $fieldset->addField(
            'video_thumb_image',
            'checkbox',
            [
                'class' => 'video_image_role',
                'label' => 'Thumbnail',
                'title' => __('Thumbnail'),
                'data-role' => 'role-type-selector',
                'value' => 'thumbnail',
            ]
        );

        $fieldset->addField(
            'video_swatch_image',
            'checkbox',
            [
                'class' => 'video_image_role',
                'label' => 'Swatch Image',
                'title' => __('Swatch Image'),
                'data-role' => 'role-type-selector',
                'value' => 'swatch_image',
            ]
        );

        $fieldset->addField(
            'new_video_disabled',
            'checkbox',
            [
                'class' => 'edited-data',
                'label' => 'Hide from Product Page',
                'title' => __('Hide from Product Page'),
                'name' => 'disabled',
            ]
        );

        $this->setForm($form);
    }

    public function getHtmlId()
    {
        if (null === $this->getData('id')) {
            $this->setData('id', $this->mathRandom->getUniqueHash('id_'));
        }
        return $this->getData('id');
    }

    /**
     * @return string
     */
    public function getWidgetOptions()
    {
        return $this->jsonEncoder->encode(
            [
                'saveVideoUrl' => $this->getUrl('catalog/product_gallery/upload'),
                'saveRemoteVideoUrl' => $this->getUrl('product_video/product_gallery/retrieveImage'),
                'htmlId' => $this->getHtmlId(),
            ]
        );
    }
}
