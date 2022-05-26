<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 1:28 AM
 */

const WECHAT_MSG_ERROR_CODE = [
    -40001 => 'Signature invalid',
    -40002 => 'XML analysis failed',
    -40003 => 'Generate signature failed',
    -40004 => 'EncodingAESKey error',
    -40005 => 'AppID invalid',
    -40006 => 'AES encrypt failed',
    -40007 => 'AES decrypt failed',
    -40008 => 'Buffer invalid',
    -40009 => 'Base64 encode failed',
    -40010 => 'Base64 decode failed',
    -40011 => 'Generate XML failed'
];

const WECHAT_MP_ERROR_CODE = [
    -41001 => 'EncodingAESKey error',
    -41003 => 'AES decrypt failed',
    -41004 => 'Buffer of decrypt invalid',
    -41005 => 'Base64 encode failed',
    -41006 => 'Base64 decode failed'
];
