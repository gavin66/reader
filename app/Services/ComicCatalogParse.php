<?php


namespace App\Services;


use App\Http\Controllers\Controller;
use App\Models\ComicFollow;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Request;

class ComicCatalogParse
{
    /**
     * 腾讯漫画
     *
     * @param $comicId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function qq( $comicId )
    {
        $url = sprintf( 'https://ac.qq.com/Comic/comicInfo/id/%s', $comicId );

        $httpResponse = ( new Client( [ 'timeout' => 8 ] ) )->get( $url );
        $html         = (string)$httpResponse->getBody();

        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );
        $xpath  = new \DOMXPath( $dom );
        $result = [
            'platform'  => 'qq',
            'is_follow' => boolval( ComicFollow::where( [ [ 'member_id', Request::user()->id ], [ 'platform', 'qq' ], [ 'comic_id', $comicId ] ] )->count() ),
            'catalog'   => [],
        ];

        foreach ( $xpath->query( "//ol[@class='chapter-page-all works-chapter-list']//a" ) as $node ) {
            // /ComicView/index/id/505430/cid/1
            $chapter = [];
            preg_match( '/\/ComicView\/index\/id\/(.*)\/cid\/(.*)/', $node->attributes->getNamedItem( 'href' )->nodeValue, $chapter );
            if ( count( $chapter ) < 3 ) {
                return Controller::responseJsonError( 40400 );
            }

            $result[ 'catalog' ][] = [
                'chapter'    => preg_replace( '/\s*/', '', $node->nodeValue ),
                'comic_id'   => $chapter[ 1 ],
                'chapter_id' => $chapter[ 2 ],
            ];
        }

        return Controller::responseJson( $result );
    }

    /**
     * 漫画粉
     *
     * @param $comicId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function manHuaFen( $comicId )
    {
        $url = sprintf( 'https://www.manhuafen.com/comic/%s/', $comicId );

        $httpResponse = ( new Client( [ 'timeout' => 8 ] ) )->get( $url );
        $html         = (string)$httpResponse->getBody();

        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );
        $xpath  = new \DOMXPath( $dom );
        $result = [
            'platform'  => 'manhuafen',
            'is_follow' => boolval( ComicFollow::where( [ [ 'member_id', Request::user()->id ], [ 'platform', 'manhuafen' ], [ 'comic_id', $comicId ] ] )->count() ),
            'catalog'   => [],
        ];

        foreach ( $xpath->query( "//div[@class='zj_list_con autoHeight']//a" ) as $node ) {
            // /comic/856/68815.html
            $chapter = [];
            preg_match( '/\/comic\/(.*)\/(.*)/', $node->attributes->getNamedItem( 'href' )->nodeValue, $chapter );
            if ( count( $chapter ) < 3 ) {
                return Controller::responseJsonError( 40400 );
            }

            $result[ 'catalog' ][] = [
                'chapter'    => $node->attributes->getNamedItem( 'title' )->nodeValue,
                'comic_id'   => $chapter[ 1 ],
                'chapter_id' => $chapter[ 2 ],
            ];
        }

        return Controller::responseJson( $result );
    }
}
