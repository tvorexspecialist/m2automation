<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class ScheduleDesignUpdateMetaProvider customizes Schedule Design Update panel
 */
class ScheduleDesignUpdate extends AbstractModifier
{
    /**#@+
     * Field names
     */
    const CODE_CUSTOM_DESIGN_FROM = 'custom_design_from';
    const CODE_CUSTOM_DESIGN_TO = 'custom_design_to';
    /**#@-*/

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        //return $meta;
        return $this->customizeDateRangeField($meta);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Customize date range field if from and to fields belong to one group
     *
     * @param array $meta
     * @return array
     */
    protected function customizeDateRangeField(array $meta)
    {
        if (
            $this->getGroupCodeByField($meta, self::CODE_CUSTOM_DESIGN_FROM)
            !== $this->getGroupCodeByField($meta, self::CODE_CUSTOM_DESIGN_TO)
        ) {
            return $meta;
        }

        $fromFieldPath = $this->arrayManager->findPath(self::CODE_CUSTOM_DESIGN_FROM, $meta, null, 'children');
        $toFieldPath = $this->arrayManager->findPath(self::CODE_CUSTOM_DESIGN_TO, $meta, null, 'children');
        $fromContainerPath = $this->arrayManager->slicePath($fromFieldPath, 0, -2);
        $toContainerPath = $this->arrayManager->slicePath($toFieldPath, 0, -2);
        $scopeLabel = $this->arrayManager->get($fromFieldPath . self::META_CONFIG_PATH . '/scopeLabel', $meta);

        $meta = $this->arrayManager->merge(
            $fromFieldPath . self::META_CONFIG_PATH,
            $meta,
            [
                'label' => __('Schedule Update From'),
                'scopeLabel' => null,
                'additionalClasses' => 'admin__field-date',
            ]
        );
        $meta = $this->arrayManager->merge(
            $toFieldPath . self::META_CONFIG_PATH,
            $meta,
            [
                'label' => __('To'),
                'scopeLabel' => null,
                'additionalClasses' => 'admin__field-date',
            ]
        );
        $meta = $this->arrayManager->merge(
            $fromContainerPath . self::META_CONFIG_PATH,
            $meta,
            [
                'label' => __('Schedule Update From'),
                'additionalClasses' => 'admin__control-grouped-date',
                'breakLine' => false,
                'component' => 'Magento_Ui/js/form/components/group',
                'scopeLabel' => $scopeLabel,
            ]
        );
        $meta = $this->arrayManager->set(
            $fromContainerPath . '/children/' . self::CODE_CUSTOM_DESIGN_TO,
            $meta,
            $this->arrayManager->get($toFieldPath, $meta)
        );

        return $this->arrayManager->remove($toContainerPath, $meta);
    }
}
