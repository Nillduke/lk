<?php
namespace App\Http\Controllers;

use App\Services\ReportSpreadsheet;
use App\Services\ReportRowImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportTransferController extends Controller
{
    public function form()
    {
        abort_unless(auth()->user()->canAccess('import_reports'), 403);
        return view('admin.import', ['categories' => DB::table('categories')->orderBy('name')->get(), 'publishers' => DB::table('publishers')->orderBy('name')->get()]);
    }

    public function template()
    {
        abort_unless(auth()->user()->canAccess('import_reports'), 403);
        $headers = ['id','title','slug','category_id','publisher_id','keyword','pages','pub_date','region','status','content','toc','tables_figures','types','applications','key_players','image_url','include_images','regenerate_content','single_user_price','multi_user_price','corporate_price','meta_title','meta_description','meta_keywords'];
        return response()->streamDownload(function () use ($headers) {
            $f = fopen('php://output', 'w'); fputcsv($f, $headers); fclose($f);
        }, 'report-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request, ReportSpreadsheet $reader, ReportRowImporter $importer)
    {
        abort_unless(auth()->user()->canAccess('import_reports'), 403);
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx|max:20480', 'category_id' => 'nullable|exists:categories,id', 'publisher_id' => 'nullable|exists:publishers,id', 'update_existing' => 'nullable|boolean', 'regenerate_content' => 'nullable|boolean', 'include_images' => 'nullable|boolean']);
        set_time_limit(180);
        $options = ['update_existing' => $request->boolean('update_existing'), 'regenerate_content' => $request->boolean('regenerate_content'), 'include_images' => $request->boolean('include_images', true), 'category_id' => $request->category_id, 'publisher_id' => $request->publisher_id];
        $headers = null; $created = 0; $updated = 0; $skipped = 0; $failed = 0; $errors = []; $number = 0;
        try {
            foreach ($reader->rows($request->file('file')->getRealPath(), $request->file('file')->getClientOriginalExtension()) as $cells) {
                $number++;
                if ($headers === null) {
                    $headers = array_map([$reader, 'normalizedHeader'], $cells);
                    if (!array_intersect(['title','slug','id'], $headers)) throw ValidationException::withMessages(['file' => 'The spreadsheet requires a title, slug or id column.']);
                    $named = array_filter($headers, fn ($name) => $name !== '');
                    if (count($named) !== count(array_unique($named))) throw ValidationException::withMessages(['file' => 'Duplicate column names were found. Use each template column only once.']);
                    continue;
                }
                if ($number > 5001) { $errors[] = 'Only the first 5,000 data rows were processed. Split larger imports.'; break; }
                if (!array_filter($cells, fn ($v) => $v !== null && trim((string)$v) !== '')) continue;
                $row = [];
                foreach ($headers as $i => $name) if ($name !== '') $row[$name] = $cells[$i] ?? null;
                try {
                    $result = $importer->import($row, $options);
                    if ($result === 'created') $created++;
                    elseif ($result === 'updated') $updated++;
                    else $skipped++;
                } catch (ValidationException $e) {
                    $failed++; if (count($errors) < 100) $errors[] = 'Row '.$number.': '.collect($e->errors())->flatten()->first();
                } catch (\Throwable $e) {
                    report($e); $failed++; if (count($errors) < 100) $errors[] = 'Row '.$number.': could not save this report. Check its values and media permissions.';
                }
            }
            if ($headers === null) throw ValidationException::withMessages(['file' => 'The spreadsheet is empty.']);
        } catch (ValidationException $e) {
            if ($created + $updated + $skipped + $failed === 0) throw $e;
            $errors[] = 'File: '.collect($e->errors())->flatten()->first();
        }
        $summary = compact('created','updated','skipped','failed','errors');
        session(['report_import' => $summary]);
        if ($request->expectsJson()) return response()->json(['status' => 1, 'data' => $summary]);
        return back()->with('success', "Import finished: $created created, $updated updated, $skipped skipped, $failed failed.")->with('import_results', $summary);
    }
}
