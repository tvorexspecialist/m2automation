<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Option;

use Magento\Framework\App\RequestInterface;

class OptionsDataProvider
{
    /**
     * @param RequestInterface $request
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getOptionsData(RequestInterface $request): array
    {
        $serializedOptions = $request->getParam('serialized_options');
        $optionsData = [];

        if ($serializedOptions) {
            $encodedOptions = json_decode($serializedOptions, JSON_OBJECT_AS_ARRAY);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Unable to unserialize options data.');
            }

            foreach ($encodedOptions as $encodedOption) {
                $decodedOptionData = [];
                parse_str($encodedOption, $decodedOptionData);
                $optionsData = array_replace_recursive($optionsData, $decodedOptionData);
            }
        }

        return $optionsData;
    }
}