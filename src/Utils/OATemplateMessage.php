<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 2:51 AM
 */

namespace MaxSky\WeChat\Utils;

use Exception;

class OATemplateMessage {

    /**
     * @param array  $struct
     * @param string ...$values
     *
     * @return array
     * @throws Exception
     */
    public static function constructTemplateMessage(array $struct, ...$values): array {
        if (count($struct) !== (func_num_args() - 1)) {
            throw new Exception('The number of struct does not match the number of args.');
        }

        $args = func_get_args();
        array_shift($args); // remove first arg `$struct`

        $result = [];

        foreach ($struct as $i => $key) {
            $result[$key] = [
                'value' => $args[$i]
            ];
        }

        return $result;
    }
}
