<?php

// Check if bcmod module exists, otherwise use this custom implementation.
if (false === function_exists('bcmod')) {
    /**
     * bcmod - get modulus (substitute for bcmod)
     * string bcmod ( string left_operand, int modulus )
     * left_operand can be really big, but be carefull with modulus :(
     * by Andrius Baranauskas and Laurynas Butkus :) Vilnius, Lithuania.
     *
     * @param $x
     * @param $y
     *
     * @return int
     *
     * @see https://stackoverflow.com/a/10626609
     */
    function bcmod($x, $y)
    {
        // how many numbers to take at once? carefull not to exceed (int)
        $take = 5;
        $mod = '';

        do {
            $a = (int)$mod.substr($x, 0, $take);
            $x = substr($x, $take);
            $mod = $a % $y;
        } while (strlen($x));

        return (int)$mod;
    }
}
