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
