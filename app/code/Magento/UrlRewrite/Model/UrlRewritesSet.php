<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * This class is to be used as a container for new generated url rewrites by adding new ones using merge method
 * Removes duplicates for a set/array of Url Rewrites based on the unique key of the url_rewrites table
 *
 */
class UrlRewritesSet
{
    /**
     * @var $rewritesArray[]
     */
    private $data = [];

    /**
     * Adds url rewrites to class data container by removing duplicates by a unique key
     *
     * @param UrlRewrite[] $urlRewritesArray
     * @return void
     */
    public function merge(array $urlRewritesArray)
    {
        $separator = '_';
        foreach ($urlRewritesArray as $urlRewrite) {
            $key = $urlRewrite->getRequestPath() . $separator . $urlRewrite->getStoreId();
            if ($key !== $separator) {
                $this->data[$key] = $urlRewrite;
            } else {
                $this->data[] = $urlRewrite;
            }
        }
    }

    /**
     * Returns the data added to container
     *
     * @return UrlRewrite[]
     */
    public function getData()
    {
        return $this->data;
    }
}
