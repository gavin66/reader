<?php


namespace App\Http\Controllers\Reader;


use App\Http\Controllers\Controller;
use App\Models\ComicFollow;
use App\Services\ComicCatalogParse;
use App\Services\ComicChapterParse;
use App\Services\ComicSearchParse;
use Illuminate\Http\Request;

class ComicController extends Controller
{
    /**
     * 搜索
     *
     * @param \Illuminate\Http\Request       $request
     * @param \App\Services\ComicSearchParse $comicSearchParse
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search( Request $request, ComicSearchParse $comicSearchParse )
    {
        if ( is_null( $platform = $request->get( 'platform' ) ) or is_null( $keywords = $request->get( 'keywords' ) ) ) {
            return self::responseJsonError( 40100 );
        }

        switch ( $platform ) {
            case 'qq':
                $result = $comicSearchParse->qq( $keywords );
                break;
            case 'manhuafen':
                $result = $comicSearchParse->manHuaFen( $keywords );
                break;
            default:
                return self::responseJsonError( 40410 );
        }

        return $result;
    }

    /**
     * 章节目录
     *
     * @param \Illuminate\Http\Request        $request
     * @param \App\Services\ComicCatalogParse $comicCatalogParse
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function catalog( Request $request, ComicCatalogParse $comicCatalogParse )
    {
        if ( is_null( $platform = $request->get( 'platform' ) )
            or is_null( $comicId = $request->get( 'comic_id' ) ) ) {
            return self::responseJsonError( 40100 );
        }

        switch ( $platform ) {
            case 'qq':
                $result = $comicCatalogParse->qq( $comicId );
                break;
            case 'manhuafen':
                $result = $comicCatalogParse->manHuaFen( $comicId );
                break;
            default:
                return self::responseJsonError( 40410 );
        }

        return $result;
    }

    /**
     * 章节图片
     *
     * @param \Illuminate\Http\Request        $request
     * @param \App\Services\ComicChapterParse $comicChapterParse
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function chapter( Request $request, ComicChapterParse $comicChapterParse )
    {
        if ( is_null( $platform = $request->get( 'platform' ) )
            or is_null( $comicId = $request->get( 'comic_id' ) )
            or is_null( $chapterId = $request->get( 'chapter_id' ) ) ) {
            return self::responseJsonError( 40100 );
        }

        switch ( $platform ) {
            case 'qq':
                $result = $comicChapterParse->qq( $comicId, $chapterId );
                break;
            case 'manhuafen':
                $result = $comicChapterParse->manHuaFen( $comicId, $chapterId );
                break;
            default:
                return self::responseJsonError( 40410 );
        }

        return $result;
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

        $followList = ComicFollow::select( [ 'comic', 'comic_id', 'chapter', 'chapter_id', 'platform' ] )
            ->where( 'member_id', $memberId )->get();

        return self::responseJson( $followList->count() ? $followList->toArray() : [] );
    }

    /**
     * 收藏
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow( Request $request )
    {
        $memberId = $request->user()->id;
        if ( is_null( $comic = $request->input( 'comic' ) )
            or is_null( $comicId = $request->input( 'comic_id' ) )
            or is_null( $platform = $request->input( 'platform' ) ) ) {
            return self::responseJsonError( 40100 );
        }

        if ( ComicFollow::where( [ [ 'member_id', $memberId ], [ 'comic_id', $comicId ] ] )->first() ) {
            return self::responseJsonError( 40320 );
        }

        $comicModel             = new ComicFollow;
        $comicModel->member_id  = $memberId;
        $comicModel->comic     = $comic;
        $comicModel->comic_id  = $comicId;
        $comicModel->chapter    = $request->input( 'chapter' );
        $comicModel->chapter_id = $request->input( 'chapter_id' );
        $comicModel->platform   = $platform;

        $comicModel->save();

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

        $followComic = ComicFollow::where( [
            [ 'member_id', $memberId ],
            [ 'comic_id', $request->input( 'comic_id' ) ],
            [ 'platform', $request->input( 'platform' ) ] ] )->first();
        if ( is_null( $followComic ) ) {
            return self::responseJsonError( 45100 );
        }

        $followComic->delete();

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
        if ( is_null( $comicId = $request->input( 'comic_id' ) )
            or is_null( $chapter = $request->input( 'chapter' ) )
            or is_null( $chapterId = $request->input( 'chapter_id' ) )
            or is_null( $platform = $request->input( 'platform' ) ) ) {
            return self::responseJsonError( 40100 );
        }

        if ( is_null( $comicModel = ComicFollow::where( [ [ 'member_id', $memberId ], [ 'comic_id', $comicId ], [ 'platform', $platform ] ] )->first() ) ) {
            return self::responseJsonError( 40216 );
        }

        $comicModel->chapter    = $chapter;
        $comicModel->chapter_id = $chapterId;
        $comicModel->save();

        return self::responseJson();
    }

}
