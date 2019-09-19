<?php


namespace App\Services;


use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

class ComicSearchParse
{
    /**
     * 腾讯漫画
     *
     * @param $keywords
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function qq( $keywords )
    {
        $url          = sprintf( 'https://ac.qq.com/Comic/searchList?search=%s', $keywords );
        $httpResponse = ( new Client( [ 'timeout' => 8 ] ) )->get( $url );
        $html         = (string)$httpResponse->getBody();

        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );
        $xpath = new \DOMXPath( $dom );
        $data  = [];
        foreach ( $xpath->query( "//ul[@class='mod_book_list mod_all_works_list mod_of']//a[@class='mod_book_cover db']" ) as $node ) {
            // 漫画名
            $comic = $node->attributes->getNamedItem( 'title' )->nodeValue;
            // 漫画 ID
            $comicIdMatch = [];
            preg_match( '/\/Comic\/comicInfo\/id\/(.*)/', $node->attributes->getNamedItem( 'href' )->nodeValue, $comicIdMatch );
            if ( count( $comicIdMatch ) < 2 ) {
                return Controller::responseJsonError( 40400 );
            }
            // 封面
            $coverNode     = $xpath->query( './img', $node )->item( 0 );
            $cover_picture = $coverNode->attributes->getNamedItem( 'data-original' )->nodeValue;
            // 最新章节
            $latestNode = $xpath->query( ".//h3[@class='mod_book_update fw']", $node )->item( 0 );
            $latest     = $latestNode->nodeValue;

            $data[] = [
                'platform'       => 'qq',
                'comic'         => $comic,
                'comic_id'      => $comicIdMatch[ 1 ],
                'cover_picture'  => $cover_picture,
                'latest_chapter' => $latest,
            ];
        }

        return Controller::responseJson( $data );
    }

    /**
     * 漫画粉
     *
     * @param $keywords
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function manHuaFen( $keywords )
    {
        $url          = 'https://api.manhuafen.com/comic/search';
        $httpResponse = ( new Client( [ 'timeout' => 8 ] ) )->post( $url, $options = [ 'form_params' => [ 'keywords' => $keywords ] ] );
        $jsonStr      = (string)$httpResponse->getBody();

        $jsonObj = json_decode( $jsonStr, true );
        if ( $jsonObj == null ) {
            return Controller::responseJsonError( 40400 );
        }

        $result = [];
        foreach ( $jsonObj[ 'items' ] as $item ) {
            $result[] = [
                'platform'          => 'manhuafen',
                'comic'             => $item[ 'name' ],
                'comic_id'          => $item[ 'id' ],
                'cover_picture'     => $item[ 'coverUrl' ],
                'latest_chapter'    => $item[ 'last_chapter_name' ],
                'latest_chapter_id' => $item[ 'last_chapter_id' ],
                'author'            => $item[ 'author' ],
            ];
        }

        return Controller::responseJson( $result );
    }
}
