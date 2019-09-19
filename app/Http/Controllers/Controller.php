<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected static $ERRORS = [
        /**
         * 请求成功
         */
        20000 => '',

        /**
         * 以下都为错误码
         */
        40100 => '参数错误',

        40210 => '新增失败',
        40216 => '更新失败',
        40220 => '删除失败',
        40226 => '上传失败',

        40300 => '帐号或密码错误',
        40310 => '登录失败',
        40320 => '已收藏过',

        40400 => '章节不存在',
        40410 => '不支持的平台',

        40600 => '图片加载失败',

        45100 => '请求失败,请重试',
        46000 => '未知错误',

        50010 => '非法的token',
    ];

    /**
     * 返回 json 数据
     *
     * @param int    $code
     * @param array  $data
     * @param string $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function responseJson( $data = [], $code = 20000, $message = '' )
    {
        return response()->json( [
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ] );
    }

    /**
     * 返回 json 格式错误信息
     *
     * @param $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function responseJsonError( $code )
    {
        return self::responseJson( [], $code, self::$ERRORS[ $code ] );
    }
}
