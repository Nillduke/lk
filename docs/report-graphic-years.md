# Report graphic periods

All standard report graphics use `config/report_graphics.php`:

- Historical: 2022-2024
- Base year: 2025
- Forecast: 2026-2035

Change these five values together for the next approved research period, then refresh Laravel configuration cache if used. Image URLs include a version hash of the period and saved content so browsers request the updated graphics. There is no date rollover based on the calendar year.

Existing report HTML is adapted on display without changing database content or image positions. The existing AMP template omits report-content images; it receives the synchronized heading but keeps that existing image-free behavior. The three recognized images are market-size.svg, region-2.svg, and the report's own legacy slug SVG or importer-generated SVG. Missing images are not inserted. Other uploaded and externally hosted analytical charts are preserved.

The market-size image is regenerated as a clearly marked illustrative XX preview with all 14 years. The regional map is retained with a common period strip. The standard taxonomy SVG retains its report title and segmentation; its illustrative chart is regenerated with 14 matching year labels and the same period strip. Custom analytical SVGs without the generator signature are returned unchanged.

New CSV/template-generated taxonomy assets are generated using the same period before content-addressed filenames are calculated. Both standard content templates use the same period for the market-size heading. Legacy adjacent market-size headings are synchronized at display time. Research prose, numerical market values, report publication dates and other historical analysis are not bulk rewritten.

The read-only image endpoint only serves active reports and sources already referenced by that report. It does not accept arbitrary file paths, external fetch targets or unpublished report IDs. Responses include ETag validation, one-hour public caching, nosniff and a restrictive SVG content policy. Local media is read first, with bounded read-only access to the legacy archive when required. No database schema or data update is required.

## Catalogue audit (2026-11-01)

Read-only audit of 85,197 active reports found 82,188 with the three standard image references and 3,009 exceptions. 346 reports have no saved content. References are checked in stored HTML; this does not establish that every referenced legacy file exists. The exception list was exported separately for editorial review; no missing charts or research content were inserted.
