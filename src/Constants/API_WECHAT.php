<?php

/**
 * Created by IntelliJ IDEA.
 * User: Max Sky
 * Date: 5/27/2022
 * Time: 1:26 AM
 */

const WECHAT_OA_GET_API_ACCESS_TOKEN = 'https://api.weixin.qq.com/cgi-bin/token';
const WECHAT_OA_GET_AUTH_ACCESS_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/access_token';
const WECHAT_OA_GET_JSAPI_TICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';
const WECHAT_OA_GET_USER_INFO = 'https://api.weixin.qq.com/cgi-bin/user/info';

const WECHAT_GET_USER_INFO_FROM_AUTH = 'https://api.weixin.qq.com/sns/userinfo';

const WECHAT_OA_ARTICLE_GET_DRAFT_COUNT = 'https://api.weixin.qq.com/cgi-bin/draft/count';
const WECHAT_OA_ARTICLE_GET_DRAFT_BATCH = 'https://api.weixin.qq.com/cgi-bin/draft/batchget';
const WECHAT_OA_ARTICLE_GET_DRAFT_BY_ID = 'https://api.weixin.qq.com/cgi-bin/draft/get';
const WECHAT_OA_ARTICLE_UPDATE_DRAFT = 'https://api.weixin.qq.com/cgi-bin/draft/update';
const WECHAT_OA_ARTICLE_PUBLISH = 'https://api.weixin.qq.com/cgi-bin/freepublish/submit';
const WECHAT_OA_ARTICLE_GET_PUBLISH_STATUS = 'https://api.weixin.qq.com/cgi-bin/freepublish/get';
const WECHAT_OA_ARTICLE_GET_PUBLISH_BATCH = 'https://api.weixin.qq.com/cgi-bin/freepublish/batchget';
const WECHAT_OA_ARTICLE_GET_PUBLISHED_BY_ID = 'https://api.weixin.qq.com/cgi-bin/freepublish/getarticle';

const WECHAT_OA_INVOICE_GET_URL = 'https://api.weixin.qq.com/card/invoice/seturl';
const WECHAT_OA_INVOICE_SET_PDF = 'https://api.weixin.qq.com/card/invoice/platform/setpdf';
const WECHAT_OA_INVOICE_INSERT_TO_CARD = 'https://api.weixin.qq.com/card/invoice/insert';

const WECHAT_OA_UTIL_GET_SHORT_URL = 'https://api.weixin.qq.com/cgi-bin/shorturl';
const WECHAT_OA_UTIL_CREATE_QRCODE = 'https://api.weixin.qq.com/cgi-bin/qrcode/create';
const WECHAT_OA_UTIL_SEND_TEMPLATE_MESSAGE = 'https://api.weixin.qq.com/cgi-bin/message/template/send';

/** Mini Program */
const WECHAT_MP_CODE_TO_SESSION = 'https://api.weixin.qq.com/sns/jscode2session';
const WECHAT_MP_GET_WXA_CODE = 'https://api.weixin.qq.com/wxa/getwxacode';
const WECHAT_MP_GET_WXA_CODE_UNLIMITED = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit';
const WECHAT_MP_SEND_SUBSCRIBE_MESSAGE = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send';

/** OCR */
const WECHAT_MP_OCR_BANKCARD = 'https://api.weixin.qq.com/cv/ocr/bankcard';
const WECHAT_MP_OCR_BIZ_LICENSE = 'https://api.weixin.qq.com/cv/ocr/bizlicense';
const WECHAT_MP_OCR_DRIVER_LICENSE = 'https://api.weixin.qq.com/cv/ocr/drivinglicense';
const WECHAT_MP_OCR_ID_CARD = 'https://api.weixin.qq.com/cv/ocr/idcard';
const WECHAT_MP_OCR_PRINTED_TEXT = 'https://api.weixin.qq.com/cv/ocr/comm';
const WECHAT_MP_OCR_VEHICLE_LICENSE = 'https://api.weixin.qq.com/cv/ocr/driving';
