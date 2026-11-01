<?php
namespace App\Http\Controllers;

use App\Services\ReportGraphics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportGraphicController extends Controller
{
    public function show(Request $request, int $id, string $kind, ReportGraphics $graphics)
    {
        $report=DB::table('reports as r')->leftJoin('reports_meta as m','m.report_id','=','r.id')
            ->where('r.id',$id)->where('r.status',1)->first(['r.id','r.slug','r.keyword','r.title','m.content']);
        abort_unless($report,404);
        $key=$request->query('source');
        abort_if($key !== null && (!is_string($key) || !preg_match('/^[a-f0-9]{20}$/D',$key)),404);
        $source=null;
        foreach ($graphics->entries($report) as $entry) {
            if ($entry['kind']===$kind && ($key===null || hash_equals($entry['key'],$key))) {
                $source=$entry['source']; break;
            }
        }
        abort_unless($source,404);
        $svg=$graphics->render($kind,$source,$report->keyword ?: preg_replace('/\s+market.*$/i','',$report->title));
        abort_unless($svg,404);
        $response=response($svg,200,[
            'Content-Type'=>'image/svg+xml; charset=UTF-8', 'X-Content-Type-Options'=>'nosniff',
            'Cache-Control'=>'public, max-age=3600',
            'Content-Security-Policy'=>"default-src 'none'; style-src 'unsafe-inline'; img-src data:",
        ]);
        $response->setEtag(hash('sha256',$svg)); $response->isNotModified($request);
        return $response;
    }
}
