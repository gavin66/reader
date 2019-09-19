<?php


namespace App\Services;


use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class ComicChapterParse
{
    /**
     * 腾讯漫画
     *
     * @param $comicId
     * @param $chapterId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function qq( $comicId, $chapterId )
    {
        $url          = sprintf( 'https://ac.qq.com/ComicView/index/id/%s/cid/%s', $comicId, $chapterId );
        $httpResponse = ( new Client( [ 'timeout' => 8 ] ) )->get( $url );
        $html         = (string)$httpResponse->getBody();

        // 匹配的 base64 字符串
        $b64MatchArr = [];
        preg_match( '/var DATA\s*=\s*\'(.*?)\'/', $html, $b64MatchArr );
        if ( count( $b64MatchArr ) < 2 ) {
            return Controller::responseJsonError( 40400 );
        }
        $b64Str = $b64MatchArr[ 1 ];

        // 解析 base64 为 json
        $jsonPartStr = '';
        for ( $i = 0; $i < mb_strlen( $b64Str ); $i++ ) {
            $jp = base64_decode( mb_substr( $b64Str, $i ), true );
            if ( $jp != false ) {
                $jsonPartStr = $jp;
                break;
            }
        }

        // 匹配 json 字符串
        $jsonMatchArr = [];
        preg_match( '/("chapter":{.*)/', $jsonPartStr, $jsonMatchArr );
        if ( count( $jsonMatchArr ) < 2 ) {
            return Controller::responseJsonError( 40400 );
        }
        $jsonStr = '{' . $jsonMatchArr[ 1 ];
        $jsonObj = json_decode( $jsonStr, true );
        if ( $jsonObj == null ) {
            return Controller::responseJsonError( 40400 );
        }
        $sourcePictures = $jsonObj[ 'picture' ];
        $pictures       = [];
        foreach ( $sourcePictures as $picture ) {
            $pictures[] = [
                'src' => env( 'IMG_PROXY_URL' ) . $picture[ 'url' ],
                'w'   => $picture[ 'width' ],
                'h'   => $picture[ 'height' ],
            ];
        }

        $result = [
            'chapter'         => $jsonObj[ 'chapter' ][ 'cTitle' ],
            'now_chapter_id'  => $jsonObj[ 'chapter' ][ 'cid' ],
            'prev_chapter_id' => $jsonObj[ 'chapter' ][ 'prevCid' ],
            'next_chapter_id' => $jsonObj[ 'chapter' ][ 'nextCid' ],
            'pictures'        => $pictures,
        ];

        return Controller::responseJson( $result );
    }

    /**
     * 漫画粉
     *
     * @param $comicId
     * @param $chapterId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function manHuaFen( $comicId, $chapterId )
    {
        $url          = sprintf( 'https://www.manhuafen.com/comic/%s/%s', $comicId, $chapterId );
        $httpResponse = ( new Client( [ 'timeout' => 8 ] ) )->get( $url );
        $html         = (string)$httpResponse->getBody();

        // title
        $titleMatchArr = [];
        preg_match( '/var pageTitle\s*=\s*"(.*?)";/', $html, $titleMatchArr );
        if ( count( $titleMatchArr ) < 2 ) {
            return Controller::responseJsonError( 40400 );
        }
        $title = $titleMatchArr[ 1 ];

        // prevChapterData
        $prevChapterMatchArr = [];
        preg_match( '/var prevChapterData\s*=\s*({.*?});/', $html, $prevChapterMatchArr );
        if ( count( $prevChapterMatchArr ) < 2 ) {
            return Controller::responseJsonError( 40400 );
        }
        $prevChapterStr = $prevChapterMatchArr[ 1 ];
        $prevChapterObj = json_decode( $prevChapterStr, true );
        if ( $prevChapterObj == null ) {
            return Controller::responseJsonError( 40400 );
        }

        // nextChapterData
        $nextChapterMatchArr = [];
        preg_match( '/var nextChapterData\s*=\s*({.*?});/', $html, $nextChapterMatchArr );
        if ( count( $nextChapterMatchArr ) < 2 ) {
            return Controller::responseJsonError( 40400 );
        }
        $nextChapterStr = $nextChapterMatchArr[ 1 ];
        $nextChapterObj = json_decode( $nextChapterStr, true );
        if ( $nextChapterObj == null ) {
            return Controller::responseJsonError( 40400 );
        }

        // pictures
        $encryptedMatchArr = [];
        preg_match( '/var chapterImages\s*=\s*"(.*?)"/', $html, $encryptedMatchArr );
        if ( count( $encryptedMatchArr ) < 2 ) {
            return Controller::responseJsonError( 40400 );
        }
        $encryptedStr = $encryptedMatchArr[ 1 ];

        $key          = '123456781234567G';
        $iv           = 'ABCDEF1G34123412';
        $decryptedStr = openssl_decrypt( $encryptedStr, 'AES-128-CBC', $key, 0, $iv );

        $sourcePictures = json_decode( $decryptedStr, true );
        if ( $sourcePictures == null ) {
            return Controller::responseJsonError( 40400 );
        }
        $pictures = [];
        foreach ( $sourcePictures as $picture ) {
            $pictures[] = [
                'src' => env( 'IMG_PROXY_URL' ) . $picture,
                'w'   => 1080,
                'h'   => 1920,
            ];
        }

        $result = [
            'chapter'         => $title,
            'now_chapter_id'  => $chapterId,
            'prev_chapter_id' => $prevChapterObj[ 'id' ] . '.html',
            'next_chapter_id' => $nextChapterObj[ 'id' ] . '.html',
            'pictures'        => $pictures,
        ];

        return Controller::responseJson( $result );
    }
}
