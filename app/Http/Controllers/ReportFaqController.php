<?php
namespace App\Http\Controllers;
use App\Services\ReportFaqs;
use App\Services\ReportOptions;
use Illuminate\Http\Request;
class ReportFaqController extends Controller
{
    public function extract(Request $request, ReportFaqs $faqs)
    {
        app(AdminController::class)->definition('reports',true);
        $data=$request->validate(['title'=>'nullable|string|max:1500','keyword'=>'nullable|string|max:100','content'=>'nullable|string|max:2000000']);
        $data['key_info']=app(ReportOptions::class)->segments($request,null);
        return response()->json(['rows'=>$faqs->generate((object)$data)]);
    }
}
