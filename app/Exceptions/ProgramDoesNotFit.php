<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when program content cannot fit its four-page layout even at the smallest
 * text size. The message is shown to the administrator as-is.
 */
class ProgramDoesNotFit extends RuntimeException
{
    public static function section(string $label): self
    {
        return new self("The {$label} has more text than fits on one page, even at the smallest text size. Shorten its content and generate the PDF again.");
    }
}
