<div class="report-option-row report-license-row" data-report-row>
<input type="hidden" name="report_licenses[{{ $rowIndex }}][id]" value="{{ $license['id']??'' }}">
<div><label for="license-{{ $rowIndex }}-name">License name</label><input id="license-{{ $rowIndex }}-name" class="form-control" name="report_licenses[{{ $rowIndex }}][lic_name]" value="{{ $license['lic_name']??'' }}" list="report-license-names" placeholder="e.g. Single User License" maxlength="100" required></div>
<div><label for="license-{{ $rowIndex }}-price">Price (USD)</label><div class="report-price-input"><span aria-hidden="true">$</span><input id="license-{{ $rowIndex }}-price" class="form-control" type="number" name="report_licenses[{{ $rowIndex }}][lic_price]" value="{{ $license['lic_price']??'' }}" min="0" max="2147483647" step="1" inputmode="numeric" placeholder="Enter price" required></div></div>
<button type="button" class="btn btn-outline-secondary report-row-remove" data-remove-report-row aria-label="Remove license">Remove</button>
</div>
