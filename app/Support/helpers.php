<?php

use Carbon\Carbon;

function site_date($value, $format = 'Y-m-d H:i:s', $timezone = null)
{
    return $value ? Carbon::parse($value)->timezone($timezone ?: config('app.timezone'))->format($format) : '';
}
function site_route($name = 'home', $parameter = null)
{
    return route($name, $parameter === null ? [] : [$parameter]);
}
function site_paragraphs($text)
{
    $text = (string) $text;

    return $text === strip_tags($text) ? nl2br(e($text)) : $text;
}
/** Normalize report formatting without consuming image or link attributes after style. */
function report_display_content($html): string
{
    $pattern = <<<'REPORT_TAGS'
~<!--[\s\S]*?-->|<(script|style|textarea|title|iframe|xmp)\b(?:[^>"']+|"[^"]*"|'[^']*')*>[\s\S]*?</\1\s*>|<[a-z][a-z0-9:-]*\b(?:[^>"']+|"[^"]*"|'[^']*')*>~i
REPORT_TAGS;
    return preg_replace_callback($pattern, function ($match) {
        if (str_starts_with($match[0], '<!--') || !empty($match[1])) return $match[0];
        // Skip quoted values belonging to other attributes, such as alt or data attributes.
        return preg_replace_callback('~"[^"]*"|\'[^\']*\'|\s+style\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)~i',
            fn ($attribute) => preg_match('/^\s/', $attribute[0]) ? '' : $attribute[0], $match[0]);
    }, (string) $html);
}

function report_forecast($text)
{
    preg_match_all('/\d{4}/', (string) $text, $m);

    return count($m[0]) > 1 ? 'Forecasts '.$m[0][0].' to '.$m[0][1] : (isset($m[0][0]) ? 'Forecasts to '.$m[0][0] : '');
}
function news_thumbnail($news)
{
    if ($news->featured_img ?? null) {
        return url($news->featured_img);
    }preg_match('/<img.+?src=[\'"](.+?)[\'"]/i', $news->content ?? '', $m);

    return $m[1] ?? url('frontend/images/news.png');
}

/** Keep report text and valid structural markup in AMP, without editor-only attributes. */
function amp_content($html)
{
    $html = site_paragraphs($html);
    $document = new \DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    try {
        $document->loadHTML('<?xml encoding="UTF-8"><div id="amp-fragment">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        foreach ($document->getElementsByTagName('*') as $element) {
            $allowed = ['id', 'class', 'title', 'role', 'href', 'target', 'rel', 'colspan', 'rowspan', 'scope'];
            foreach (iterator_to_array($element->attributes) as $attribute) {
                if (!in_array($attribute->name, $allowed, true) && !str_starts_with($attribute->name, 'aria-')) $element->removeAttribute($attribute->name);
            }
        }
        $root = $document->getElementById('amp-fragment');
        $output = '';
        foreach ($root->childNodes as $child) $output .= $document->saveHTML($child);
        return $output;
    } finally {
        libxml_clear_errors(); libxml_use_internal_errors($previous);
    }
}
