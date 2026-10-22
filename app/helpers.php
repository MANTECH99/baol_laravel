<?php

if (!function_exists('django_date')) {
    /**
     * Formatte une date comme le fait Django avec son DATE_FORMAT par défaut "N j, Y".
     * "N" = abréviation de mois en style Associated Press (MONTHS_AP) pour l locale "en".
     *
     * Exemples : 12 October 2025 -> "Oct. 12, 2025"
     *            1 March 2025    -> "March 1, 2025"
     *            5 Sept 2025     -> "Sept. 5, 2025"
     */
    function django_date($date): string
    {
        if (!$date) {
            return '';
        }

        $monthsAp = [
            1 => 'Jan.',
            2 => 'Feb.',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'Aug.',
            9 => 'Sept.',
            10 => 'Oct.',
            11 => 'Nov.',
            12 => 'Dec.',
        ];

        if (is_string($date)) {
            $date = \Illuminate\Support\Carbon::parse($date);
        }

        return sprintf(
            '%s %d, %d',
            $monthsAp[(int) $date->format('n')],
            (int) $date->format('j'),
            (int) $date->format('Y')
        );
    }
}
