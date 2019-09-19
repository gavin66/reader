<?php

namespace App\Providers;

use Illuminate\Auth\GenericUser;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app[ 'auth' ]->viaRequest( 'api', function ( $request ) {
            if ( Str::startsWith( $request->path(), 'api' ) ) {
                // api 接口
                if ( $token = $request->header( 'x-token' ) ) {
                    try {
                        if ( $member = Cache::get( 'token:' . decrypt( $token ) ) ) {
                            return new GenericUser( $member );
                        }
                    } catch ( DecryptException $exception ) {
                        // 捕获解密异常
                        // 会返回 50010 状态码
                    }
                }
            } elseif ( Str::startsWith( $request->path(), 'admin' ) ) {
                // 管理后台接口
                if ( $adminToken = $request->header( 'x-token' ) ) {
                    $username = decrypt( $adminToken );

                    if ( $admin = Cache::get( 'admin-token:' . $username ) ) {
                        return new GenericUser( $admin );
                    }
                }
            }
        } );
    }
}
