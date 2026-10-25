<div class="report-option-row report-segment-row" data-report-row>
<input type="hidden" name="report_segments[{{ $rowIndex }}][key]" value="{{ $segment['key']??'' }}">
<div><label for="segment-{{ $rowIndex }}-label">Segment name</label><input id="segment-{{ $rowIndex }}-label" class="form-control" name="report_segments[{{ $rowIndex }}][label]" value="{{ $segment['label']??'' }}" list="report-segment-names" placeholder="e.g. Product Type" maxlength="100" required></div>
<div><label for="segment-{{ $rowIndex }}-value">Options or companies</label><textarea id="segment-{{ $rowIndex }}-value" class="form-control" name="report_segments[{{ $rowIndex }}][value]" rows="3" placeholder="Enter options separated by commas or line breaks">{{ $segment['value']??'' }}</textarea></div>
<button type="button" class="btn btn-outline-secondary report-row-remove" data-remove-report-row aria-label="Remove segment">Remove</button>
</div>
