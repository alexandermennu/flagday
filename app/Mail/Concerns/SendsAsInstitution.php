<?php

namespace App\Mail\Concerns;

use Illuminate\Mail\Mailables\Address;

/**
 * Sends from the configured MAIL_FROM_ADDRESS, but always under the institution's name
 * ("Starz University" by default, from config/graduation.php) — so the sender display
 * name can't drift back to an old MAIL_FROM_NAME / APP_NAME left in an environment.
 */
trait SendsAsInstitution
{
    protected function institutionSender(): Address
    {
        return new Address(config('mail.from.address'), config('graduation.institution'));
    }
}
