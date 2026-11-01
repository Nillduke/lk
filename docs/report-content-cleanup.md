# Manual report content cleanup

Admin URL: `/authuser/reports/content-cleanup` (also in the Reports toolbar and navigation).
Requires both `view_reports` and `edit_reports`. No schema migration or content cleanup runs during deployment.

1. Choose up to 5,000 reports (the default), status, industry, optional name and cleanup rules.
2. Preview the proposed passages. A preview does not change any report.
3. Select individual reports or Select all, then Apply to selected reports.
4. Preview the next batch, or skip the current batch. Start from the beginning to revisit skipped reports.

Each request searches a window of at most 200 report IDs. Only a small matched batch is loaded for review.
Long content fields over 1 MB and reports with duplicate metadata rows require the individual editor.

Rules deliberately match complete generic COVID coverage sentences and the two standard COVID TOC headings.
TOC numbers and existing HTML are preserved. Analysis, historical statements and unmatched wording require
individual editorial review. Reports with COVID, coronavirus, SARS-CoV or pandemic in their titles are protected.
Generation templates are unchanged; this tool operates only on explicitly selected stored reports.

Small previews expire after 20 minutes. Large batches expire after two hours. A session-bound random token and source fingerprints prevent arbitrary IDs,
replay and stale previews. Changes are recomputed server-side, locked and written in a transaction. Reports
changed since preview are skipped. Only changed fields and modification timestamps are written.

Verification: isolated SQLite feature tests cover preview-only operation, selected updates, markup and historical
text preservation, numbering, protected subjects, stale edits, permissions, batch size/cursor, oversized records,
expired previews and replay. Production UI verification uses preview only; no live cleanup is applied.

## Large batches

Sizes 100, 500, 1,000 and 5,000 use an incremental preview and apply workflow.
The browser drives short requests with progress and pause/resume controls; no queue worker or cron is needed.
The complete selected batch is processed, while previews display up to 50 reports per page. Select all eligible
reports applies across the entire batch. Individual selections persist across pages.

Temporary private working files in `storage/app/private/report-cleanup` contain preview snippets, source/result
fingerprints, selections and progress, never full report bodies. Ownership and report permissions are checked on
every request. Working files are ignored by Git and JSON files older than one day are pruned when a new batch starts.
Each preview step loads at most 50 matching reports; each apply request processes at most 25 with short per-report
transactions. Retry detection prevents repeating changes when a response was interrupted after a committed update.
Refresh during an apply operation offers Resume applying; it does not start new updates automatically.

Scale verification: an isolated 5,001-report fixture previews and updates exactly 5,000, preserves report 5,001 and
historical analysis, paginates details, and checks continuation, ownership, protected subjects and retry recovery.
