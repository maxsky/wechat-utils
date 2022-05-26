<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 1:51 AM
 */

namespace MaxSky\WeChat\Utils\Traits;

use Exception;

trait RandomUtil {

    /**
     * @param int $length
     *
     * @return string
     */
    private function str_random(int $length = 16): string {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            try {
                $bytes = random_bytes($size);
            } catch (Exception $e) {
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

                $result = '';

                $max = strlen($chars) - 1;

                for ($i = 0; $i < $length; $i++) {
                    $result .= $chars[mt_rand(0, $max)];
                }

                return $result;
            }

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}
