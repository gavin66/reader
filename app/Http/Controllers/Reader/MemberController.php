<?php


namespace App\Http\Controllers\Reader;


use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class MemberController extends Controller
{
    /**
     * 用户登录
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login( Request $request )
    {
        $email    = $request->input( 'email' );
        $password = $request->input( 'password' );

        if ( empty( $email ) or empty( $password ) ) {
            return $this->responseJsonError( 40100 );
        }

        $member = Member::where( [ [ 'email', '=', $email ] ] )->first();
        if ( $member and decrypt( $member->password ) === $password ) {
            // 加密帐号名成为 token
            $token = encrypt( $email );

            // 以帐号名为 key 缓存用户基本信息(因为帐号名唯一)  30天过期
            Cache::put( $this->tokenCacheKey( $email ), Arr::except( $member->toArray(), [ 'password' ] ), 2592000 );

            // 返回 json 数据
            return $this->responseJson( [ 'token' => $token ] );
        }

        return $this->responseJsonError( 40300 );
    }

    /**
     * 用户信息
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info( Request $request )
    {
        $member = $request->user();

        return $this->responseJson( [
            'roles'        => $member->roles,
            'introduction' => $member->introduction,
            'avatar'       => $member->avatar,
            'name'         => $member->name,
        ] );
    }

    /**
     * 登出
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout( Request $request )
    {
        $member = $request->user();

        Cache::forget( $this->tokenCacheKey( $member->username ) );

        return $this->responseJson();
    }

    /**
     * 重设密码
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function retypePassword( Request $request )
    {
        $member = $request->user();

        if ( is_null( $oldPassword = $request->input( 'old_password' ) )
            or is_null( $password = $request->input( 'password' ) ) ) {
            return $this->responseJsonError( 40100 );
        }

        $user = Member::find( $member->id );
        if ( $user and decrypt( $user->password ) === $oldPassword ) {
            $user->password = encrypt( $password );
            $user->save();
        } else {
            return $this->responseJsonError( 40100 );
        }

        return $this->responseJson();
    }

    /**
     * 获取 token令牌的缓存key
     *
     * @param $name
     *
     * @return string
     */
    private function tokenCacheKey( $name )
    {
        return 'token:' . $name;
    }

}
