<?php
/**
 * Contact module base controller
 */
namespace Magento\Contact\Model;

/**
 * Email from contact form
 *
 * @api
 */
interface MailInterface
{
    /**
     * Send email from contact form
     *
     * @param string $replyTo Reply-to email address
     * @param array $variables Email template variables
     * @return void
     */
    public function send($replyTo, array $variables);
}
