<?php


namespace App\Http\Controllers\Reader;


use App\Http\Controllers\Controller;
use App\Models\NovelFollow;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class NovelController extends Controller
{
    /**
     * 搜索小说
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search( Request $request )
    {
        if ( is_null( $query = $request->get( 'query' ) ) ) {
            return self::responseJsonError( 40100 );
        }

        $httpResponse = ( new Client( [ 'timeout' => 8 ] ) )
            ->get( 'http://www.b5200.net/modules/article/search.php', [ 'query' => [ 'searchkey' => $query ] ] );
        $html         = (string)$httpResponse->getBody();

        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );
        $xpath  = new \DOMXPath( $dom );
        $result = [];
        foreach ( $xpath->query( "//table[@class='grid']/tr[position()>1]" ) as $node ) {
            // 小说
            $novelNode = $xpath->query( './td[1]/a', $node )->item( 0 );
            $novel     = $novelNode->nodeValue;
            $novelUrl  = $novelNode->attributes->getNamedItem( 'href' )->nodeValue;
            // 最新章节
            $recentChapterNode = $xpath->query( './td[2]/a', $node )->item( 0 );
            $recentChapter     = $recentChapterNode->nodeValue;
            $recentChapterUrl  = $recentChapterNode->attributes->getNamedItem( 'href' )->nodeValue;
            // 作者
            $author = $xpath->query( './td[3]', $node )->item( 0 )->nodeValue;
            // 更新时间
            $updatedDate = $xpath->query( './td[5]', $node )->item( 0 )->nodeValue;

            $result[] = [
                'novel'              => $novel,
                'novel_url'          => $novelUrl,
                'recent_chapter'     => $recentChapter,
                'recent_chapter_url' => $recentChapterUrl,
                'author'             => $author,
                'updated_date'       => $updatedDate,
            ];
        }

        return self::responseJson( $result );
    }

    /**
     * 小说章节目录
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function catalog( Request $request )
    {
        if ( is_null( $url = $request->get( 'url' ) ) ) {
            return self::responseJsonError( 40100 );
        }

        $httpResponse = ( new Client( [ 'timeout' => 8 ] ) )->get( $url );
        $html         = (string)$httpResponse->getBody();

        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );
        $xpath  = new \DOMXPath( $dom );
        $result = [
            'is_follow' => boolval( NovelFollow::where( 'catalog_url', $url )->count() ),
            'catalog'   => [],
        ];

        foreach ( $xpath->query( "//div[@id='list']/dl/dd[position()>9]" ) as $node ) {
            $chapterNode = $xpath->query( './a', $node )->item( 0 );
            $chapter     = $chapterNode->nodeValue;
            $chapterUrl  = $chapterNode->attributes->getNamedItem( 'href' )->nodeValue;

            $result[ 'catalog' ][] = [
                'chapter'     => $chapter,
                'chapter_url' => $chapterUrl,
            ];
        }

        return self::responseJson( $result );
    }

    /**
     * 小说章节内容
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function chapter( Request $request )
    {
        if ( is_null( $url = $request->get( 'url' ) ) ) {
            return self::responseJsonError( 40100 );
        }

        $httpResponse = ( new Client( [ 'timeout' => 8 ] ) )->get( $url );
        $html         = (string)$httpResponse->getBody();

        $dom = new \DOMDocument();
        @$dom->loadHTML( $html );
        $xpath  = new \DOMXPath( $dom );
        $result = [];

        $result[ 'title' ] = $xpath->query( "//div[@class='bookname']/h1" )->item( 0 )->nodeValue;
        foreach ( $xpath->query( "//div[@id='content']/p" ) as $node ) {
            $result[ 'content' ][] = $node->nodeValue;
        }
        $result[ 'novel_url' ]    = $xpath->query( "//div[@class='bottem2']/a[position()=3]/@href" )->item( 0 )->nodeValue;
        $result[ 'previous_url' ] = $xpath->query( "//div[@class='bottem2']/a[position()=2]/@href" )->item( 0 )->nodeValue;
        $result[ 'next_url' ]     = $xpath->query( "//div[@class='bottem2']/a[position()=4]/@href" )->item( 0 )->nodeValue;

        return self::responseJson( $result );
    }

    /**
     * 收藏列表
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function followList( Request $request )
    {
        $memberId = $request->user()->id;

        $followList = NovelFollow::select( [ 'novel', 'catalog_url', 'chapter', 'chapter_url' ] )->where( 'member_id', $memberId )->get();

        return self::responseJson( $followList->count() ? $followList->toArray() : [] );
    }

    /**
     * 收藏小说
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow( Request $request )
    {
        $memberId = $request->user()->id;
        if ( is_null( $novel = $request->input( 'novel' ) ) or is_null( $catalogUrl = $request->input( 'catalog_url' ) ) ) {
            return self::responseJsonError( 40100 );
        }

        if ( NovelFollow::where( [ [ 'member_id', $memberId ], [ 'catalog_url', $catalogUrl ] ] )->first() ) {
            return self::responseJsonError( 40320 );
        }

        $novelStore              = new NovelFollow;
        $novelStore->member_id   = $memberId;
        $novelStore->novel       = $novel;
        $novelStore->catalog_url = $catalogUrl;
        $novelStore->chapter     = $request->input( 'chapter' );
        $novelStore->chapter_url = $request->input( 'chapter_url' );

        $novelStore->save();

        return self::responseJson();
    }

    /**
     * 取消收藏
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unfollow( Request $request )
    {
        $memberId = $request->user()->id;

        $followNovel = NovelFollow::where( [ [ 'member_id', $memberId ], [ 'catalog_url', $request->input( 'catalog_url' ) ] ] )->first();
        if ( is_null( $followNovel ) ) {
            return self::responseJsonError( 45100 );
        }

        $followNovel->delete();

        return self::responseJson();
    }

    /**
     * 更新阅读进度
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function progress( Request $request )
    {
        $memberId = $request->user()->id;
        if ( is_null( $catalogUrl = $request->input( 'catalog_url' ) )
            or is_null( $chapter = $request->input( 'chapter' ) )
            or is_null( $chapterUrl = $request->input( 'chapter_url' ) ) ) {
            return self::responseJsonError( 40100 );
        }

        if ( is_null( $novelStore = NovelFollow::where( [ [ 'member_id', $memberId ], [ 'catalog_url', $catalogUrl ] ] )->first() ) ) {
            return self::responseJsonError( 40216 );
        }

        $novelStore->chapter     = $chapter;
        $novelStore->chapter_url = $chapterUrl;
        $novelStore->save();

        return self::responseJson();
    }


}
