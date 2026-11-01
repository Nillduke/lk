<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportCleanupBatchJob
{
    private string $directory;

    public function __construct(private ReportContentCleanup $cleanup)
    {
        $this->directory = config('reports.cleanup_job_path') ?? storage_path('app/private/report-cleanup');
        if (!is_dir($this->directory) && !mkdir($this->directory, 0700, true) && !is_dir($this->directory)) throw new \RuntimeException('Cannot create cleanup working directory.');
    }

    private function path(string $id, string $suffix = '.json'): string
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{40}$/D', $id), 404);
        return $this->directory.'/'.$id.$suffix;
    }

    private function write(string $path, array $data): void
    {
        $temp = tempnam($this->directory, 'write-');
        try {
            chmod($temp, 0600);
            if (file_put_contents($temp, json_encode($data, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE)) === false || !rename($temp, $path)) throw new \RuntimeException('Could not save cleanup progress.');
        } finally { if (is_file($temp)) unlink($temp); }
    }

    private function withJob(string $id, int $owner, callable $callback): mixed
    {
        $path = $this->path($id);
        abort_unless(is_file($path), 404);
        $lock = fopen($this->path($id, '.lock'), 'c');
        if (!$lock || !flock($lock, LOCK_EX)) throw new \RuntimeException('Cannot lock cleanup progress.');
        try {
            $state = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            abort_unless($state['owner'] === $owner, 403);
            abort_if($state['expires'] < now()->timestamp, 419, 'This batch expired. Please preview a new batch.');
            $result = $callback($state);
            $this->write($path, $state);
            return $result;
        } finally { flock($lock, LOCK_UN); fclose($lock); }
    }

    public function start(array $filters, int $owner): string
    {
        // Temporary preview working files only; never report-content backups or public assets.
        foreach (glob($this->directory.'/*.json') ?: [] as $old) {
            if (filemtime($old) < time() - 86400 && preg_match('/^[A-Za-z0-9]{40}(?:-\d+)?\.json$/D', basename($old))) unlink($old);
        }
        $id = Str::random(40);
        $state = ['id'=>$id,'owner'=>$owner,'filters'=>$filters,'target'=>(int)$filters['batch_size'],
            'after'=>(int)$filters['after'],'more'=>true,'phase'=>'previewing','expires'=>now()->addHours(2)->timestamp,
            'pages'=>0,'rows'=>[],'selected'=>[],'done'=>[],'updated'=>0,'skipped'=>0,'already_current'=>0];
        $this->write($this->path($id), $state);
        return $id;
    }

    private function summary(array $state): array
    {
        return ['id'=>$state['id'],'phase'=>$state['phase'],'target'=>$state['target'],'matched'=>count($state['rows']),
            'eligible'=>count(array_filter($state['rows'], fn($r)=>$r['eligible'])), 'selected'=>count($state['selected']),
            'processed'=>count($state['done']),'updated'=>$state['updated'],'skipped'=>$state['skipped'],
            'already_current'=>$state['already_current'],'pages'=>$state['pages'],'after'=>$state['after'],'more'=>$state['more']];
    }

    public function scan(string $id, int $owner): array
    {
        return $this->withJob($id, $owner, function (&$state) {
            if ($state['phase'] !== 'previewing') return $this->summary($state);
            $filters = array_replace($state['filters'], ['after'=>$state['after'],'batch_size'=>min(50, $state['target'] - count($state['rows']))]);
            $batch = $this->cleanup->batch($filters);
            if ($batch['rows']) {
                $page = ++$state['pages'];
                $this->write($this->path($state['id'], '-'.$page.'.json'), $batch['rows']);
                foreach ($batch['rows'] as $row) $state['rows'][$row['id']] = ['fingerprint'=>$row['fingerprint'],
                    'result_fingerprint'=>$row['result_fingerprint'],'eligible'=>!$row['reason'] && count($row['changes']) > 0,'page'=>$page];
            }
            $state['after'] = $batch['after']; $state['more'] = $batch['more'];
            if (count($state['rows']) >= $state['target'] || !$batch['more']) $state['phase'] = 'ready';
            return $this->summary($state);
        });
    }

    public function page(string $id, int $owner, int $page = 1): array
    {
        return $this->withJob($id, $owner, function (&$state) use ($page) {
            abort_if($page < 1 || $page > max(1, $state['pages']), 404);
            $rows = $state['pages'] ? json_decode(file_get_contents($this->path($state['id'], '-'.$page.'.json')), true, 512, JSON_THROW_ON_ERROR) : [];
            foreach ($rows as &$row) {
                $row['selected'] = isset($state['selected'][$row['id']]);
                $row['result'] = $state['done'][$row['id']] ?? null;
            }
            unset($row);
            return ['progress'=>$this->summary($state),'rows'=>$rows,'page'=>$page,'filters'=>$state['filters']];
        });
    }

    public function select(string $id, int $owner, string $mode, int $page = 1, array $ids = []): array
    {
        return $this->withJob($id, $owner, function (&$state) use ($mode, $page, $ids) {
            abort_unless($state['phase'] === 'ready', 409, 'Finish the preview before selecting reports.');
            if ($mode === 'all') {
                $state['selected'] = [];
                foreach ($state['rows'] as $reportId=>$row) if ($row['eligible']) $state['selected'][$reportId] = true;
            } elseif ($mode === 'none') {
                $state['selected'] = [];
            } else {
                abort_unless($mode === 'page' && $page >= 1 && $page <= $state['pages'], 422);
                foreach ($ids as $reportId) abort_unless(isset($state['rows'][$reportId]) && $state['rows'][$reportId]['page'] === $page && $state['rows'][$reportId]['eligible'], 422);
                foreach ($state['rows'] as $reportId=>$row) if ($row['page'] === $page) unset($state['selected'][$reportId]);
                foreach ($ids as $reportId) $state['selected'][$reportId] = true;
            }
            return $this->summary($state);
        });
    }

    public function beginApply(string $id, int $owner): array
    {
        return $this->withJob($id, $owner, function (&$state) {
            if (in_array($state['phase'], ['applying','complete'])) return $this->summary($state);
            abort_unless($state['phase'] === 'ready' && count($state['selected']) > 0, 422, 'Select reports to update first.');
            $state['phase'] = 'applying';
            return $this->summary($state);
        });
    }

    public function applyStep(string $id, int $owner): array
    {
        return $this->withJob($id, $owner, function (&$state) {
            if ($state['phase'] === 'complete') return $this->summary($state);
            abort_unless($state['phase'] === 'applying', 409);
            $pending = array_diff(array_keys($state['selected']), array_keys($state['done'])); sort($pending, SORT_NUMERIC);
            foreach (array_slice($pending, 0, 25) as $reportId) {
                $result = DB::transaction(function () use ($reportId, $state) {
                    $source = $this->cleanup->load($reportId, true);
                    if (!$source) return 'skipped';
                    $original = $state['rows'][$reportId];
                    // Recover safely if a connection failed after a commit but before progress was saved.
                    if (hash_equals($original['result_fingerprint'], $this->cleanup->contentFingerprint($source))) return 'already_current';
                    $plan = $this->cleanup->plan($source, $state['filters']);
                    if ($plan['reason'] || !$plan['changes'] || !hash_equals($original['fingerprint'], $plan['fingerprint'])) return 'skipped';
                    foreach ($plan['updates'] as $table=>$updates) {
                        if (!$updates) continue;
                        DB::table($table)->where('id', $table === 'reports' ? $reportId : $source['meta']->id)->update($updates + ['updated_at'=>now()]);
                    }
                    if (!$plan['updates']['reports']) DB::table('reports')->where('id',$reportId)->update(['updated_at'=>now()]);
                    return 'updated';
                });
                $state['done'][$reportId] = $result; $state[$result]++;
            }
            if (count($state['done']) === count($state['selected'])) $state['phase'] = 'complete';
            return $this->summary($state);
        });
    }
}
