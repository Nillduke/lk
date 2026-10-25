<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReportSpreadsheet
{
    public function rows($path, $extension): \Generator
    {
        if (in_array(strtolower($extension), ['csv', 'txt'])) {
            $f = fopen($path, 'r');
            try {
                while (($row = fgetcsv($f)) !== false) {
                    yield $row;
                }
            } finally {
                fclose($f);
            }

return;
        }
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['file' => 'The XLSX archive could not be read.']);
        }
        try {
            $total = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $total += $stat['size'];
                if ($total > 128 * 1024 * 1024) {
                    throw ValidationException::withMessages(['file' => 'The expanded workbook exceeds 128 MB.']);
                }
            }
            $strings = [];
            $xml = $zip->getFromName('xl/sharedStrings.xml');
            if ($xml !== false) {
                $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET);
                foreach ($doc->si as $si) {
                    $text = '';
                    foreach ($si->xpath('.//*[local-name()="t"]') as $t) {
                        $text .= (string) $t;
                    }$strings[] = $text;
                }
            }
            $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($xml === false) {
                throw ValidationException::withMessages(['file' => 'The workbook must contain a first worksheet.']);
            }
            $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET);
            if (! $doc) {
                throw ValidationException::withMessages(['file' => 'Invalid worksheet XML.']);
            }
            foreach ($doc->sheetData->row as $r) {
                $row = [];
                foreach ($r->c as $c) {
                    preg_match('/[A-Z]+/', (string) $c['r'], $m);
                    $index = 0;
                    foreach (str_split($m[0] ?? 'A') as $char) {
                        $index = $index * 26 + ord($char) - 64;
                    }$index--;
                    if ($index > 200) {
                        throw ValidationException::withMessages(['file' => 'Workbooks may contain at most 201 columns.']);
                    }
                    $value = (string) $c->v;
                    $type = (string) $c['t'];
                    if ($type === 's') {
                        $value = $strings[(int) $value] ?? '';
                    }if ($type === 'inlineStr') {
                        $value = '';
                        foreach ($c->xpath('.//*[local-name()="t"]') as $t) {
                            $value .= (string) $t;
                        }
                    }$row[$index] = $value;
                }
                if ($row) {
                    $full = array_fill(0, max(array_keys($row)) + 1, '');
                    foreach ($row as $i => $value) {
                        $full[$i] = $value;
                    }yield $full;
                }
            }
        } finally {
            $zip->close();
        }
    }

    public function normalizedHeader($header): string
    {
        $raw = trim((string) $header, "\xEF\xBB\xBF \t\n\r");
        $raw = Str::replace([' ', '-'], '_', $raw);
        $key = $raw === strtoupper($raw) ? strtolower($raw) : Str::snake($raw);
        $key = preg_replace('/_+/', '_', strtolower($key));

        return ['reports_title' => 'title', 'report_title' => 'title', 'report_id' => 'id', 'url_slug' => 'slug', 'category' => 'category_id', 'publisher' => 'publisher_id', 'date' => 'pub_date', 'publication_date' => 'pub_date', 'price' => 'single_user_price', 'single_user_license' => 'single_user_price', 'multi_user_license' => 'multi_user_price', 'corporate_license' => 'corporate_price', 'corporate_user_license' => 'corporate_price', 'image' => 'image_url', 'image_src' => 'image_url', 'report_image' => 'image_url'][$key] ?? $key;
    }
}
