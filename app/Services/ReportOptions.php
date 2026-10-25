<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReportOptions
{
    public function label(string $key): string { return Str::title(str_replace(['_', '-'], ' ', $key)); }
    public function value($value): string
    {
        if (!is_array($value)) return (string)$value;
        return implode(', ', array_map(fn ($item) => $this->value($item), $value));
    }
    public function rows(?object $record): array
    {
        $values = json_decode($record->key_info ?? '', true) ?: [];
        $rows = [];
        foreach ($values as $key => $value) $rows[] = ['key' => $key, 'label' => $this->label((string)$key), 'value' => $this->value($value)];
        return $rows;
    }
    public function segments(Request $request, ?object $record): string
    {
        $rows = $request->input('report_segments', []);
        Validator::make(['report_segments' => $rows], ['report_segments' => 'array|max:50', 'report_segments.*' => 'array', 'report_segments.*.key' => 'nullable|string|max:150', 'report_segments.*.label' => 'nullable|string|max:100', 'report_segments.*.value' => 'nullable|string|max:30000'])->validate();
        $previous = json_decode($record->key_info ?? '', true) ?: [];
        $values = [];
        foreach ($rows as $index => $row) {
            $label = trim($row['label'] ?? ''); $value = $row['value'] ?? ''; $key = $row['key'] ?? '';
            if ($label === '' && trim($value) === '') continue;
            if ($label === '') throw ValidationException::withMessages(['report_segments.'.$index.'.label' => 'Enter a name for each report segment.']);
            if ($key !== '' && array_key_exists($key, $previous) && $label === $this->label($key)) $name = $key;
            else $name = Str::snake(preg_replace('/[^\pL\pN\s_]+/u', ' ', $label));
            if ($name === '') throw ValidationException::withMessages(['report_segments.'.$index.'.label' => 'Use letters or numbers in the segment name.']);
            if (array_key_exists($name, $values)) throw ValidationException::withMessages(['report_segments.'.$index.'.label' => 'Each segment needs a different name.']);
            // Keep original values/types unchanged when the user only opens and saves.
            if (array_key_exists($key, $previous) && $value === $this->value($previous[$key])) $values[$name] = $previous[$key];
            else $values[$name] = trim(preg_replace('/\s*\R\s*/u', ', ', $value));
        }
        if ($values === $previous && $record) return $record->key_info ?? '{}';
        return json_encode((object)$values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    public function licenses(Request $request, ?object $record): ?array
    {
        if ($request->has('report_licenses_present')) $rows = $request->input('report_licenses', []);
        elseif ($request->has('licenses_json')) {
            $rows = json_decode($request->input('licenses_json'), true);
            if (!is_array($rows) || !array_is_list($rows)) throw ValidationException::withMessages(['licenses_json' => 'The license list could not be read.']);
        } else return null;
        Validator::make(['report_licenses' => $rows], ['report_licenses' => 'array|max:20', 'report_licenses.*' => 'array', 'report_licenses.*.id' => 'nullable|integer', 'report_licenses.*.lic_name' => 'nullable|string|max:100', 'report_licenses.*.lic_price' => 'nullable|integer|min:0|max:2147483647'])->validate();
        $existing = $record ? DB::table('reports_licenses')->where('report_id', $record->id)->get()->keyBy('id') : collect();
        $seen = []; $ids = []; $licenses = [];
        foreach ($rows as $index => $row) {
            $name = trim($row['lic_name'] ?? ''); $price = $row['lic_price'] ?? null; $id = $row['id'] ?? null;
            if ($name === '' && ($price === null || $price === '')) continue;
            if ($name === '' || $price === null || $price === '') throw ValidationException::withMessages(['report_licenses.'.$index.'.lic_name' => 'Enter a license name and price, or remove the unused row.']);
            $normalized = Str::lower($name);
            if (isset($seen[$normalized])) throw ValidationException::withMessages(['report_licenses.'.$index.'.lic_name' => 'Each license needs a different name.']);
            $seen[$normalized] = true;
            if ($id && !$existing->has((int)$id)) throw ValidationException::withMessages(['report_licenses' => 'A license does not belong to this report. Reload the page and try again.']);
            // Legacy API requests identify licenses by name, rather than their ID.
            if (!$id) $id = $existing->first(fn ($license) => Str::lower($license->lic_name) === $normalized)->id ?? null;
            if ($id && isset($ids[$id])) throw ValidationException::withMessages(['report_licenses' => 'The same license was submitted twice.']);
            if ($id) $ids[$id] = true;
            $licenses[] = ['id' => $id, 'lic_name' => $name, 'lic_price' => (int)$price];
        }
        $removed = $existing->keys()->diff(array_keys($ids));
        if ($removed->isNotEmpty() && DB::table('checkouts')->whereIn('report_license_id', $removed)->exists()) throw ValidationException::withMessages(['report_licenses' => 'A license used in an order cannot be removed. You can update its name or price.']);
        return $licenses;
    }
    public function saveLicenses(int $reportId, array $licenses): void
    {
        $keep = array_values(array_filter(array_column($licenses, 'id')));
        $delete = DB::table('reports_licenses')->where('report_id', $reportId);
        if ($keep) $delete->whereNotIn('id', $keep);
        $delete->delete();
        foreach ($licenses as $license) {
            $data = ['lic_name' => $license['lic_name'], 'lic_price' => $license['lic_price']];
            if ($license['id']) DB::table('reports_licenses')->where('report_id', $reportId)->where('id', $license['id'])->update($data);
            else DB::table('reports_licenses')->insert($data + ['report_id' => $reportId]);
        }
    }
}
