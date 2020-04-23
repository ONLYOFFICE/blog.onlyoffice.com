<?php
/**
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2017 AddThis, LLC                                     |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 */

if (!function_exists('array_replace_recursive')) {
    /**
     * The array_replace_recursive function is only available in PHP 5 >= 5.3.0.
     * Defining it here when necessary.
     *
     * @param array $array  an array
     * @param array $array1 an array
     *
     * @return array
     */
    function array_replace_recursive($array, $array1)
    {
        if (!function_exists('addthisRecurse')) {
            /**
             * A recursive function for array_replace_recursive
             *
             * @param array $array  an array
             * @param array $array1 an array
             *
             * @return array
             */
            function addthisRecurse($array, $array1)
            {
                foreach ($array1 as $key => $value) {
                    if (!isset($array[$key])
                        || (isset($array[$key]) && !is_array($array[$key]))
                    ) {
                        $array[$key] = array();
                    }

                    if (is_array($value)) {
                        $value = addthisRecurse($array[$key], $value);
                    }

                    $array[$key] = $value;
                }

                return $array;
            }
        }

        // handle the arguments, merge one by one
        $args = func_get_args();
        $array = $args[0];
        if (!is_array($array)) {
            return $array;
        }
        for ($i = 1; $i < count($args); $i++) {
            if (is_array($args[$i])) {
                $array = addthisRecurse($array, $args[$i]);
            }
        }

        return $array;
    }
}