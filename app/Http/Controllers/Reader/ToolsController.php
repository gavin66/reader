<?php


namespace App\Http\Controllers\Reader;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Facades\Image;

class ToolsController extends Controller
{
    /**
     * 搜索
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function prictureProxy( Request $request )
    {
        $url = $request->input( 'url' );

        try {
            $img = Image::make( $url );
        } catch ( NotReadableException $exception ) {
            return self::responseJsonError( 40600 );
        }

        return $img->response();
    }

}
