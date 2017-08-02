<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate\Inline;

/**
 * Controls and represents the  state of the inline translation processing.
 *
 * @api
 * @since 2.0.0
 */
interface StateInterface
{
    /**
     * Disable inline translation
     *
     * @return void
     * @since 2.0.0
     */
    public function disable();

    /**
     * Enable inline translation
     *
     * @return void
     * @since 2.0.0
     */
    public function enable();

    /**
     * Check if inline translation enabled/disabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled();

    /**
     * Suspend inline translation
     *
     * Store current inline translation status
     * and apply new status or disable inline translation.
     *
     * @param bool $status
     * @return void
     * @since 2.0.0
     */
    public function suspend($status = false);

    /**
     * Disable inline translation
     *
     * Restore inline translation status
     * or apply new status.
     *
     * @param bool $status
     * @return void
     * @since 2.0.0
     */
    public function resume($status = true);
}
