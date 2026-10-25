<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function definition($resource, $write = false)
    {
        $def = config('admin.'.$resource);
        abort_unless($def, 404);
        abort_unless(auth()->user()->canAccess($write ? ($def['write'] ?? $def['permission']) : $def['permission']), 403);
        $def['pk'] = $def['pk'] ?? 'id';
        $def['resource'] = $resource;

        return $def;
    }

    public function dashboard()
    {
        $counts = [];
        foreach (config('admin') as $resource => $def) {
            if (auth()->user()->canAccess($def['permission'])) {
                $counts[$def['label']] = DB::table($def['table'])->count();
            }
        }

return view('admin.dashboard', compact('counts'));
    }

    private function query($def, Request $request)
    {
        $q = DB::table($def['table']);
        $cols = Schema::getColumnListing($def['table']);
        $search = array_intersect(['title', 'headline', 'name', 'email', 'email_id', 'full_name', 'order_number', 'Title', 'slug'], $cols);
        if ($request->filled('s')) {
            $q->where(function ($q) use ($search, $request) {
                foreach ($search as $field) {
                    $q->orWhere($field, 'like', '%'.$request->s.'%');
                }
            });
        }
        if ($request->filled('status') && in_array('status', $cols)) {
            $q->where('status', $request->status);
        }
        foreach (['category_id', 'publisher_id', 'type', 'payment_status'] as $field) {
            if ($request->filled($field) && in_array($field, $cols)) {
                $q->where($field, $request->input($field));
            }
        }

        return $q;
    }

    public function index(Request $request, $resource)
    {
        $def = $this->definition($resource);
        $rows = $this->query($def, $request)->orderByDesc($def['pk'])->paginate(25)->withQueryString();
        if ($request->expectsJson()) {
            return response()->json(['status' => 1, 'data' => $rows->through(function ($row) {
                unset($row->password,$row->verification_code);

                return $row;
            })]);
        }

        return view('admin.index', compact('def', 'rows'));
    }

    private function find($def, $id)
    {
        $cols = Schema::getColumnListing($def['table']);

        return DB::table($def['table'])->where(in_array('uuid', $cols) && ! ctype_digit((string) $id) ? 'uuid' : $def['pk'], $id)->first();
    }

    public function edit(Request $request, $resource, $id = null)
    {
        $def = $this->definition($resource, true);
        $id = $id ?: $request->input('id', $request->input('uuid'));
        $record = $id ? $this->find($def, $id) : null;
        abort_if($id && ! $record, 404);
        $fields = array_values(array_intersect($def['fields'], Schema::getColumnListing($def['table'])));
        $meta = $resource === 'reports' && $record ? DB::table('reports_meta')->where('report_id', $record->id)->first() : null;
        $licenses = $resource === 'reports' && $record ? DB::table('reports_licenses')->where('report_id', $record->id)->get() : collect();
        $categories = DB::table('categories')->orderBy('name')->get();
        $publishers = DB::table('publishers')->orderBy('name')->get();
        $roles = auth()->user()->canAccess('manage_access') ? DB::table('rbac_roles')->get() : collect();
        $permissions = auth()->user()->canAccess('manage_access') ? DB::table('rbac_permissions')->get() : collect();
        $assigned = $resource === 'users' && $record ? DB::table('rbac_userroles')->where('UserID', $record->id)->pluck('RoleID')->all() : [];
        $assignedPermissions = $resource === 'roles' && $record ? DB::table('rbac_rolepermissions')->where('RoleID', $record->ID)->pluck('PermissionID')->all() : [];

        return view('admin.edit', compact('def', 'record', 'fields', 'meta', 'licenses', 'categories', 'publishers', 'roles', 'permissions', 'assigned', 'assignedPermissions'));
    }

    public function save(Request $request, $resource, $id = null)
    {
        $def = $this->definition($resource, true);
        $record = $id ? $this->find($def, $id) : null;
        abort_if($id && ! $record, 404);
        if ($record && $request->isMethod('patch')) {
            $request->merge(array_merge((array) $record, $request->all()));
        }
        if ($resource === 'reports') {
            $request->validate(['title' => 'required|string|max:1500', 'category_id' => 'required|exists:categories,id', 'publisher_id' => 'required|exists:publishers,id', 'slug' => 'nullable|string|max:150', 'pub_date' => 'required|date', 'pages' => 'nullable|integer|min:0']);
        }
        if ($resource === 'users') {
            $request->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:50|unique:users,email,'.($record->id ?? 'NULL'), 'password' => ($record ? 'nullable' : 'required').'|string|min:10']);
        }
        if ($resource === 'publishers') {
            $request->validate(['name' => 'required|string|max:100', 'contact_name' => 'required|string|max:100', 'contact_email' => 'required|email|max:100']);
        }
        if ($resource === 'categories') {
            $request->validate(['name' => 'required|string|max:50']);
        }
        if ($resource === 'news') {
            $request->validate(['headline' => 'required|string|max:100', 'content' => 'required|string', 'category_id' => 'required|exists:categories,id']);
        }
        if (in_array($resource, ['roles', 'permissions'])) {
            $request->validate(['Title' => 'required|string|max:128', 'Description' => 'required|string']);
        }
        if ($resource === 'news') {
            $request->validate(['featured_img_file' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:10240', 'featured_img' => ['nullable', 'string', 'max:1000', function ($attribute, $value, $fail) {
                if (!preg_match('~^(?:https?://|/?(?:uploads|storage|frontend)/)~i', $value) || str_contains($value, '..')) $fail('Use a local image path or an HTTP(S) image URL.');
            }]]);
        }
        $reportOptions = app(\App\Services\ReportOptions::class);
        $licenseRows = $resource === 'reports' ? $reportOptions->licenses($request, $record) : null;
        $columns = Schema::getColumns($def['table']);
        $names = array_column($columns, 'name');
        $data = $request->only(array_intersect($def['fields'], $names));
        foreach ($data as $k => $v) {
            if ($v === null) {
                $data[$k] = '';
            }if (is_array($v)) {
                $data[$k] = json_encode($v);
            }
        }
        if ($resource === 'reports' && $request->has('report_segments_present')) {
            $data['key_info'] = $reportOptions->segments($request, $record);
        }
        if ($resource === 'news' && $request->hasFile('featured_img_file')) {
            $data['featured_img'] = $this->storeMedia($request->file('featured_img_file'), true)['url'];
        }
        if (in_array('slug', $names) && empty($data['slug']) && (!$record || $request->exists('slug'))) {
            $data['slug'] = Str::limit(Str::slug($data['title'] ?? $data['headline'] ?? $data['name'] ?? $data['Title'] ?? Str::random(8)), 150, '');
        }
        if ($resource === 'users') {
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
        }
        if (! $record && in_array('uuid', $names)) {
            $data['uuid'] = (string) Str::uuid();
        }
        if (! $record && in_array('created_at', $names)) {
            $data['created_at'] = now();
        }if (in_array('updated_at', $names)) {
            $data['updated_at'] = now();
        }
        if (! $record && in_array('user_id', $names)) {
            $data['user_id'] = auth()->id();
        }
        if ($resource === 'checkouts' && ! $record) {
            $data['checkout_id'] = DB::table('checkouts')->max('checkout_id') + 1;
            $data['order_number'] = $data['order_number'] ?: 'ORD'.Str::upper(Str::random(12));
        }
        // Validate mandatory columns before writing, including fields required by the imported schema.
        foreach ($columns as $col) {
            if (! $record && ! $col['nullable'] && ! $col['auto_increment'] && $col['default'] === null && ! in_array($col['name'], ['Lft', 'Rght']) && ! array_key_exists($col['name'], $data)) {
                $request->validate([$col['name'] => 'required']);
            }
        }
        $saved = DB::transaction(function () use ($def, $record, $data, $resource, $request, $reportOptions, $licenseRows) {
            if (in_array($resource, ['roles', 'permissions']) && ! $record) {
                $max = DB::table($def['table'])->max('Rght') ?? 0;
                DB::table($def['table'])->where($def['pk'], 1)->increment('Rght', 2);
                $data['Lft'] = $max;
                $data['Rght'] = $max + 1;
            }
            if ($record) {
                $id = $record->{$def['pk']};
                DB::table($def['table'])->where($def['pk'], $id)->update($data);
            } else {
                $id = DB::table($def['table'])->insertGetId($data, $def['pk']);
            }
            if ($resource === 'reports') {
                $meta = $request->only(['content', 'toc', 'tables_figures']);
                if ($meta) {
                    DB::table('reports_meta')->updateOrInsert(['report_id' => $id], $meta + ['updated_at' => now(), 'created_at' => now()]);
                }
                if ($licenseRows !== null) $reportOptions->saveLicenses((int)$id, $licenseRows);
            }
            if ($resource === 'users' && $request->has('role_ids')) {
                abort_unless(auth()->user()->canAccess('manage_access'), 403);
                $roles = array_values(array_filter(array_map('intval', (array) $request->role_ids)));
                if ($id === auth()->id()) {
                    abort_unless(in_array(1, $roles), 422, 'Keep your own root access.');
                }DB::table('rbac_userroles')->where('UserID', $id)->delete();
                foreach ($roles as $role) {
                    abort_unless(DB::table('rbac_roles')->where('ID', $role)->exists(), 422);
                    DB::table('rbac_userroles')->insert(['UserID' => $id, 'RoleID' => $role, 'AssignmentDate' => time()]);
                }
            }
            if ($resource === 'roles' && $request->has('permission_ids')) {
                DB::table('rbac_rolepermissions')->where('RoleID', $id)->delete();
                foreach (array_filter((array) $request->permission_ids) as $permission) {
                    abort_unless(DB::table('rbac_permissions')->where('ID', $permission)->exists(), 422);
                    DB::table('rbac_rolepermissions')->insert(['RoleID' => $id, 'PermissionID' => $permission, 'AssignmentDate' => time()]);
                }
            }

            return $id;
        });
        if ($request->expectsJson()) {
            return response()->json(['status' => 1, 'message' => 'Saved', 'id' => $saved]);
        }

return redirect()->route('admin.index', $resource)->with('success', 'Saved successfully.');
    }

    public function show(Request $request, $resource, $id)
    {
        $def = $this->definition($resource);
        $row = $this->find($def, $id);
        abort_unless($row, 404);
        unset($row->password,$row->verification_code);

        return response()->json(['status' => 1, 'data' => $row]);
    }

    public function metadata(Request $request, $id)
    {
        $def = $this->definition('reports', true);
        $report = $this->find($def, $id);
        abort_unless($report, 404);
        $data = $request->validate(['content' => 'sometimes|nullable|string', 'toc' => 'sometimes|nullable|string', 'tables_figures' => 'sometimes|nullable|string']);
        DB::table('reports_meta')->updateOrInsert(['report_id' => $report->id], $data + ['updated_at' => now(), 'created_at' => now()]);

        return response()->json(['status' => 1, 'message' => 'Metadata updated']);
    }

    public function delete(Request $request, $resource, $id = null)
    {
        $def = $this->definition($resource, true);
        $ids = $id ? [$id] : (array) $request->input('ids', $request->input('chkrow', []));
        abort_unless($ids, 422);
        DB::transaction(function () use ($ids, $resource, $def) {
            foreach ($ids as $id) {
                $row = $this->find($def, $id);
                abort_unless($row, 404);
                $key = $row->{$def['pk']};
                if (in_array($resource, ['categories', 'publishers']) && DB::table('reports')->where($resource === 'categories' ? 'category_id' : 'publisher_id', $key)->exists()) {
                    throw ValidationException::withMessages(['record' => 'This record is still referenced by reports. Reassign those reports first.']);
                }if ($resource === 'users') {
                    abort_if($key === auth()->id(), 422, 'You cannot delete your signed-in account.');
                }if (in_array($resource, ['roles', 'permissions'])) {
                    abort_if((int) $key === 1, 422, 'The root entry is protected.');
                }
                if ($resource === 'reports') {
                    DB::table('reports_meta')->where('report_id', $key)->delete();
                    DB::table('reports_licenses')->where('report_id', $key)->delete();
                }
                if ($resource === 'roles') {
                    DB::table('rbac_userroles')->where('RoleID', $key)->delete();
                    DB::table('rbac_rolepermissions')->where('RoleID', $key)->delete();
                }
                if ($resource === 'permissions') {
                    DB::table('rbac_rolepermissions')->where('PermissionID', $key)->delete();
                }
                if ($resource === 'users') {
                    DB::table('rbac_userroles')->where('UserID', $key)->delete();
                }DB::table($def['table'])->where($def['pk'], $key)->delete();
            }
        });

        return $request->expectsJson() ? response()->json(['status' => 1, 'message' => 'Deleted']) : back()->with('success', 'Deleted successfully.');
    }

    public function bulk(Request $request, $resource)
    {
        if (! $request->has('ids') && $request->has('chkrow')) {
            $request->merge(['ids' => $request->input('chkrow')]);
        }
        if ($request->input('action', $request->input('for')) === 'delete') {
            return $this->delete($request, $resource);
        }
        $def = $this->definition($resource, true);
        $data = $request->validate(['ids' => 'required|array|max:500', 'ids.*' => 'integer', 'status' => 'required|in:0,1,2,3,4,5,6']);
        abort_unless(Schema::hasColumn($def['table'], 'status'), 422);
        if ($resource === 'users' && (string) $data['status'] === '0' && in_array(auth()->id(), $data['ids'])) {
            throw ValidationException::withMessages(['ids' => 'You cannot deactivate your signed-in account.']);
        }DB::table($def['table'])->whereIn($def['pk'], $data['ids'])->update(['status' => $data['status']]);

        return $request->expectsJson() ? response()->json(['status' => 1, 'message' => 'Updated']) : back()->with('success', 'Selected records updated.');
    }

    public function export(Request $request, $resource)
    {
        $def = $this->definition($resource);
        if ($resource === 'reports') {
            abort_unless(auth()->user()->canAccess('export_reports'), 403);
        }
        $columns = array_values(array_diff(Schema::getColumnListing($def['table']), ['password', 'verification_code', 'verification_expiry_dt']));
        $q = $this->query($def, $request)->select($columns)->orderBy($def['pk']);

        return response()->streamDownload(function () use ($q, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($q->cursor() as $row) {
                $values = [];
                foreach ($columns as $column) {
                    $v = (string) ($row->$column ?? '');
                    $values[] = preg_match('/^[=+\-@]/', $v) ? "'".$v : $v;
                }fputcsv($file,$values);
            }fclose($file);
        }, $resource.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function authorizeMedia(Request $request): void
    {
        // Authorize the module being edited, rather than requiring report access for news/CMS.
        $resource = $request->input('resource', 'reports');
        abort_unless(in_array($resource, ['reports', 'news', 'pages', 'publishers']), 422);
        if ($resource === 'reports' && auth()->user()->canAccess('import_reports')) return;
        $this->definition($resource, true);
    }

    private function storeMedia(\Illuminate\Http\UploadedFile $file, bool $image): array
    {
        $directory = 'uploads/'.($image ? 'images/' : 'files/').now()->format('Y/m');
        $name = $file->hashName();
        $file->move(public_path($directory), $name);
        $path = $directory.'/'.$name;

        return ['status' => 1, 'url' => '/'.$path, 'location' => '/'.$path, 'file' => $path];
    }

    public function uploadImage(Request $request)
    {
        $this->authorizeMedia($request);
        $request->validate(['file' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:10240']);

        return response()->json($this->storeMedia($request->file('file'), true));
    }

    public function upload(Request $request)
    {
        $this->authorizeMedia($request);
        $request->validate(['file' => 'required|file|max:20480|mimes:jpg,jpeg,png,gif,webp,pdf,csv,xlsx']);
        $file = $request->file('file');
        $image = str_starts_with($file->getMimeType() ?? '', 'image/');

        return response()->json($this->storeMedia($file, $image));
    }
}
