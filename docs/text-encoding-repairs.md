# Text encoding repairs - 2026-11-01

The public website contained UTF-8 text decoded as Windows-1252/Latin-1, sometimes repeatedly or through HTML entities. Privacy Policy, Terms and Conditions, About Us, FAQs, How to Order, and two report-generation templates were repaired at source. Legal wording and report numbers were preserved.

The live primary database was repaired directly, using optimistic comparisons inside row-lock transactions. No schema changes were required. The secondary report database was read only. Automatic decoding repaired 1,701 report records and 1,635 report-metadata records. Missing letters and punctuation were then recovered from matching `reportdb.exceldata` records: 1348 distinct reports using exact token matches, followed by whole-fragment matches on 60 reports (overlapping the earlier set). Remaining company names and symbols were reviewed individually on 25 reports. Three entries containing only an unrecoverable replacement-character placeholder were removed, without inventing company names. Published dates, prices, URLs, and readable research figures were retained.

The exact data operations are mirrored in `database/project-db-update.sql`, using hexadecimal UTF-8 literals to avoid encoding damage during transfer. The SQL also supports escaped JSON strings. The PHP utilities are preferable for future audits because they process small batches and retain private audit ledgers:

```sh
php tools/repair-text-encoding.php
php tools/repair-text-encoding.php --apply
php tools/restore-text-from-original-reports.php
php tools/restore-text-from-original-reports.php --apply
```

Both tools default to auditing. The original-source tool requires the existing read-only `reportdb` connection. It matches exact report slugs and only uses unambiguous source text; it rejects sources still containing replacement characters or question marks. Runtime ledgers are under `storage/app/private`, outside Git and the public webroot. Do not run SQL repairs and browser/admin content edits concurrently.

The decoder preserves valid Unicode, HTML markup, escaped HTML and scientific symbols, handles repeated encoding, and never strips arbitrary non-ASCII characters. Five unit tests cover 20 assertions, including valid multilingual text, JSON, HTML entity safety, and damaged closing quotes. The public information pages were checked in a browser; the report title TNF-alpha symbol also displays correctly.

Additional spelling references used in the reviewed exceptions:
- https://www.ciamuller.com.br/upload/files/nota-comercial-2025/demonstracoes-financeiras-2024-cmb-2294.pdf
- https://www.topsoe.com/hubfs/DOWNLOADS/DOWNLOADS%20-%20Annual%20reports/Haldor%20Tops%C3%B8e%20Holding%20Sustainability%20Report%202018.pdf
- https://lanificiodellolivo.com/wp-content/uploads/2020/07/02-CERTIFICATO-OCS-scadenza-090421.pdf
- https://ec.europa.eu/competition/mergers/cases/additional_data/m9321_63_3.pdf
