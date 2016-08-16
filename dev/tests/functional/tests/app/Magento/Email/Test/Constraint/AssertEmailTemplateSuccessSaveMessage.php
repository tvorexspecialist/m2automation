<?php

namespace Magento\Email\Test\Constraint;

use Magento\Email\Test\Page\Adminhtml\EmailTemplateIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assertion to check Success Save Message for Email Template.
 */
class AssertEmailTemplateSuccessSaveMessage extends AbstractConstraint
{
    /**
     * Check Success Save Message for Email Template.
     *
     * @param EmailTemplateIndex $emailTemplateIndex
     * @return void
     */
    public function processAssert(EmailTemplateIndex $emailTemplateIndex)
    {
        $actualMessage = $emailTemplateIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text success save message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that success message is displayed.';
    }
}