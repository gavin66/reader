<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//$router->get( '/', function () use ( $router ) {
//    return $router->app->version();
//} );

$router->group( [ 'namespace' => 'Reader', 'prefix' => 'api' ], function () use ( $router ) {
    // 登录
    $router->post( 'user/login', 'MemberController@login' );

    // 用户已登录
    $router->group( [ 'middleware' => 'auth' ], function () use ( $router ) {
        /**
         * 用户
         */
        $router->group( [ 'prefix' => 'user' ], function () use ( $router ) {
            // 用户信息
            $router->get( 'info', 'MemberController@info' );
            // 退出登录
            $router->post( 'logout', 'MemberController@logout' );
            // 重设密码
            $router->put( 'password', 'MemberController@retypePassword' );
        } );

        /**
         * 小说
         */
        $router->group( [ 'prefix' => 'novel' ], function () use ( $router ) {
            // 搜索
            $router->get( 'search', 'NovelController@search' );
            // 目录
            $router->get( 'catalog', 'NovelController@catalog' );
            // 章节
            $router->get( 'chapter', 'NovelController@chapter' );
            // 收藏列表
            $router->get( 'follow', 'NovelController@followList' );
            // 收藏
            $router->post( 'follow', 'NovelController@follow' );
            // 取消收藏
            $router->post( 'unfollow', 'NovelController@unfollow' );
            // 更新阅读进度
            $router->post( 'progress', 'NovelController@progress' );
        } );

        /**
         * 漫画
         */
        $router->group( [ 'prefix' => 'comic' ], function () use ( $router ) {
            // 搜索
            $router->get( 'search', 'ComicController@search' );
            // 目录
            $router->get( 'catalog', 'ComicController@catalog' );
            // 章节
            $router->get( 'chapter', 'ComicController@chapter' );
            // 收藏列表
            $router->get( 'follow', 'ComicController@followList' );
            // 收藏
            $router->post( 'follow', 'ComicController@follow' );
            // 取消收藏
            $router->post( 'unfollow', 'ComicController@unfollow' );
            // 更新阅读进度
            $router->post( 'progress', 'ComicController@progress' );
        } );

    } );

    /**
     * 工具
     */
    $router->group( [ 'prefix' => 'tools' ], function () use ( $router ) {
        $router->get( 'picture/proxy', 'ToolsController@prictureProxy' );
    } );

} );


