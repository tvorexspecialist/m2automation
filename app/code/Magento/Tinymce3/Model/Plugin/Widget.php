<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Model\Plugin;

use Magento\Tinymce3\Model\Config\Source\Wysiwyg\Editor;

/**
 * @todo remove this plugin
 */
class Widget
{
    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    private $activeEditor;

    /**
     * @param \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
     */
    public function __construct(
        \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
    ) {
        $this->activeEditor = $activeEditor;
    }

    /**
     * @param \Magento\Widget\Model\Widget $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetPlaceholderImageUrl(
        \Magento\Widget\Model\Widget $subject,
        $result
    ) {
        if ($this->activeEditor->getWysiwygAdapterPath() === Editor::WYSIWYG_EDITOR_CONFIG_VALUE) {
            $placeholder_ext_strlen = strlen('.' . pathinfo($result, PATHINFO_EXTENSION));
            $placeholder_sans_ext = substr(
                $result,
                0,
                strlen($result) - $placeholder_ext_strlen
            );

            $result = $placeholder_sans_ext . '_tinymce3.png';
        }

        return $result;
    }
}
