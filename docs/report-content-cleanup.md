# Manual report content cleanup

Admin URL: `/authuser/reports/content-cleanup` (also in the Reports toolbar and navigation).
Requires both `view_reports` and `edit_reports`. No schema migration or content cleanup runs during deployment.

1. Choose 10, 25 or 50 reports, status, industry, optional name and cleanup rules.
2. Preview the proposed passages. A preview does not change any report.
3. Select individual reports or Select all, then Apply to selected reports.
4. Preview the next batch, or skip the current batch. Start from the beginning to revisit skipped reports.

Each request searches a window of at most 200 report IDs. Only a small matched batch is loaded for review.
Long content fields over 1 MB and reports with duplicate metadata rows require the individual editor.

Rules deliberately match complete generic COVID coverage sentences and the two standard COVID TOC headings.
TOC numbers and existing HTML are preserved. Analysis, historical statements and unmatched wording require
individual editorial review. Reports with COVID, coronavirus, SARS-CoV or pandemic in their titles are protected.
Generation templates are unchanged; this tool operates only on explicitly selected stored reports.

Previews expire after 20 minutes. A session-bound random token and source fingerprints prevent arbitrary IDs,
replay and stale previews. Changes are recomputed server-side, locked and written in a transaction. Reports
changed since preview are skipped. Only changed fields and modification timestamps are written.

Verification: isolated SQLite feature tests cover preview-only operation, selected updates, markup and historical
text preservation, numbering, protected subjects, stale edits, permissions, batch size/cursor, oversized records,
expired previews and replay. Production UI verification uses preview only; no live cleanup is applied.
