<?php

namespace App\Helpers;

class NumberHelper
{
    public static function convert($number)
    {
        $number = number_format($number, 2, '.', '');
        $split = explode('.', $number);
        $whole = (int) $split[0];
        $fraction = $split[1];

        $formatter = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
        $text = $formatter->format($whole);

        return mb_strtoupper($text . " PESOS " . $fraction . "/100 MN");
    }
}
