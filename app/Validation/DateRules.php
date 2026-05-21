<?php

namespace App\Validation;

class DateRules
{
    /**
     * Vérifie que la date/datetime fournie est postérieure à maintenant.
     */
    public function after_now(?string $str = null, ?string &$error = null): bool
    {
        if ($str === null || $str === '') {
            $error = lang('Validation.after_now');
            return false;
        }

        $timestamp = strtotime($str);

        if ($timestamp === false) {
            $error = lang('Validation.after_now');
            return false;
        }

        if ($timestamp <= time() + 60) {
            $error = lang('Validation.after_now');
            return false;
        }

        return true;
    }
}