<?php
namespace App\Http\Controllers;

use App\Services\ReportContentCleanup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportCleanupController extends Controller
{
    private function authorizeEditor(): void
    {
        abort_unless(auth()->user()->canAccess('view_reports') && auth()->user()->canAccess('edit_reports'), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeEditor();
        return view('admin.report-cleanup', ['categories' => DB::table('categories')->orderBy('name')->get(), 'filters' => $request->query(), 'batch' => null]);
    }

    public function preview(Request $request, ReportContentCleanup $cleanup)
    {
        $this->authorizeEditor();
        $filters = $request->validate([
            'after' => 'required|integer|min:0', 'batch_size' => ['required','integer',Rule::in([10,25,50])],
            'status' => ['required',Rule::in(['active','inactive','all'])], 'category_id' => 'nullable|integer|exists:categories,id',
            'search' => 'nullable|string|max:150', 'sentences' => 'required|boolean', 'toc' => 'required|boolean',
        ]);
        $filters += ['category_id' => null, 'search' => '']; $filters['search'] = trim($filters['search'] ?? '');
        $batch = $cleanup->batch($filters);
        $token = Str::random(48);
        $request->session()->put('report_cleanup_preview', ['token' => $token, 'expires' => now()->addMinutes(20)->timestamp,
            'filters' => $filters, 'after' => $batch['after'], 'rows' => collect($batch['rows'])->mapWithKeys(fn($r) => [$r['id'] => ['fingerprint' => $r['fingerprint'], 'eligible' => !$r['reason'] && count($r['changes']) > 0]])->all()]);
        // Long source content and proposed HTML are never stored in the session or sent as editable payloads.
        foreach ($batch['rows'] as &$row) unset($row['updates']);
        unset($row);
        return view('admin.report-cleanup', ['categories' => DB::table('categories')->orderBy('name')->get(), 'filters' => $filters, 'batch' => $batch, 'token' => $token]);
    }

    public function apply(Request $request, ReportContentCleanup $cleanup)
    {
        $this->authorizeEditor();
        $data = $request->validate(['token' => 'required|string', 'ids' => 'required|array|min:1|max:50', 'ids.*' => 'required|integer|distinct']);
        $preview = $request->session()->get('report_cleanup_preview');
        abort_unless($preview && hash_equals($preview['token'], $data['token']) && $preview['expires'] >= now()->timestamp, 419, 'This preview expired. Please preview the batch again.');
        foreach ($data['ids'] as $id) abort_unless($preview['rows'][$id]['eligible'] ?? false, 422, 'Only eligible reports from this preview can be selected.');
        $saved = 0; $skipped = 0;
        DB::transaction(function () use ($cleanup, $data, $preview, &$saved, &$skipped) {
            $ids = $data['ids']; sort($ids, SORT_NUMERIC);
            foreach ($ids as $id) {
                $source = $cleanup->load($id, true);
                if (!$source) { $skipped++; continue; }
                $plan = $cleanup->plan($source, $preview['filters']);
                if ($plan['reason'] || !hash_equals($preview['rows'][$id]['fingerprint'], $plan['fingerprint']) || !$plan['changes']) { $skipped++; continue; }
                foreach ($plan['updates'] as $table => $updates) {
                    if (!$updates) continue;
                    $rowId = $table === 'reports' ? $id : $source['meta']->id;
                    DB::table($table)->where('id', $rowId)->update($updates + ['updated_at' => now()]);
                }
                if (!$plan['updates']['reports']) DB::table('reports')->where('id', $id)->update(['updated_at' => now()]);
                $saved++;
            }
        });
        $request->session()->forget('report_cleanup_preview');
        return redirect()->route('admin.cleanup.index', array_merge($preview['filters'], ['after' => $preview['after']]))
            ->with('success', $saved.' selected reports updated. '.$skipped.' changed or unavailable reports skipped. Preview the next batch when ready.');
    }
}
