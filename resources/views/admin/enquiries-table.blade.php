@push('styles')
<style>
.admin-app .leads-table{min-width:1650px;font-size:13px}
.admin-app .leads-table th,.admin-app .leads-table td{white-space:nowrap;overflow-wrap:normal;max-width:none;vertical-align:middle;padding:12px 10px}
.admin-app .leads-table th{color:#526176;font-size:12px;background:#f7f9fc}
.admin-app .leads-table .lead-summary{display:block;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.admin-app .leads-table .lead-message{max-width:220px}
.admin-app .leads-table .lead-actions{display:inline-flex;gap:6px;align-items:center}
.admin-app .leads-table .lead-actions form{display:inline;margin:0}
</style>
@endpush
<table class="table table-hover leads-table" aria-label="Website leads">
<thead><tr><th><input type="checkbox" aria-label="Select all" onclick="document.querySelectorAll('.row-select').forEach(el=>el.checked=this.checked)"></th><th>ID</th><th>Contact person</th><th>Email</th><th>Phone</th><th>Company</th><th>Country</th><th>Lead type</th><th>Report</th><th>Message</th><th>Received</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
@forelse($rows as $row)
@php($report = $enquiryReports->get($row->report_id))
<tr>
<td><input class="row-select" type="checkbox" name="ids[]" form="bulkform" value="{{ $row->id }}" aria-label="Select record"></td>
<td>{{ $row->id }}</td>
<td><strong>{{ $row->full_name ?: '?' }}</strong></td>
<td><a href="mailto:{{ $row->email_id }}">{{ $row->email_id }}</a></td>
<td>{{ $row->contact_no ?: '?' }}</td>
<td>{{ $row->company ?: '?' }}</td>
<td>{{ $row->country ?: '?' }}</td>
<td>{{ \App\Mail\WebsiteEnquiry::label((string)$row->type) }}</td>
<td>@if($report)<a class="lead-summary" href="{{ route('rd_view',$report->slug) }}" title="{{ $report->title }}" target="_blank" rel="noopener">{{ $report->title }}</a>@else<span>General enquiry</span>@endif</td>
<td><span class="lead-summary lead-message" title="{{ $row->message }}">{{ $row->message ?: '?' }}</span></td>
<td>{{ $row->created_at ?: '?' }}</td>
<td><span class="badge">{{ $row->status }}</span></td>
<td><div class="lead-actions"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.edit',['enquiries',$row->id]) }}">Edit</a><form method="post" action="{{ route('admin.delete',['enquiries',$row->id]) }}" onsubmit="return confirm('Delete this record?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></div></td>
</tr>
@empty
<tr><td colspan="13">No matching leads.</td></tr>
@endforelse
</tbody></table>
