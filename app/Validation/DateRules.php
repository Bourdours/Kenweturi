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

    /**
     * Vérifie que les jours de récurrence sélectionnés sont valides.
     */
    public function valid_recurring_days($days, ?string &$error = null): bool
    {
        if (empty($days)) {
            return true;
        }

        // Si ce n'est pas un tableau (ex: chaîne seule), on le convertit
        $daysArray = is_array($days) ? $days : [$days];
        $allowedDays = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];

        foreach ($daysArray as $day) {
            if (!in_array($day, $allowedDays)) {
                $error = "Un ou plusieurs jours de récurrence sélectionnés sont invalides.";
                return false;
            }
        }

        return true;
    }

    /**
     * Vérifie que les semaines de récurrence sélectionnées sont valides (de 1 à 4).
     */
    public function valid_recurring_weeks($weeks, ?string &$error = null): bool
    {
        if (empty($weeks)) {
            return true;
        }

        $weeksArray = is_array($weeks) ? $weeks : [$weeks];

        foreach ($weeksArray as $week) {
            $weekInt = (int) $week;
            if ($weekInt < 1 || $weekInt > 4) {
                $error = "Le numéro de semaine doit être compris entre 1 et 4.";
                return false;
            }
        }

        return true;
    }
}