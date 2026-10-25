<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportSearchTest extends TestCase
{
    use DatabaseTransactions;

    public function test_report_search_keeps_words_intact_matches_keywords_and_excludes_inactive_reports(): void
    {
        $ids = DB::table('reports')->limit(2)->pluck('id');
        DB::table('reports')->where('id', $ids[0])->update(['title' => 'ZqxSearch Supermarket Forecast', 'keyword' => 'ZqxKeywordOnly', 'status' => 1]);
        DB::table('reports')->where('id', $ids[1])->update(['title' => 'ZqxSearch Supermarket Forecast', 'keyword' => 'ZqxKeywordOnly', 'status' => 0]);

        foreach (['supermarket ZqxSearch', 'ZqxKeywordOnly'] as $term) {
            $result = $this->getJson('/api/autocomplete/search?s='.urlencode($term))->assertOk()->assertJsonCount(1, 'data.reports');
            $result->assertJsonPath('data.reports.0.id', $ids[0]);
            $this->get('/searchfor?s='.urlencode($term))->assertOk()->assertViewHas('list', fn ($rows) => count($rows) === 1 && $rows[0]->id === $ids[0]);
        }
    }

    public function test_direct_keyword_matches_rank_before_newer_incidental_title_matches(): void
    {
        $ids = DB::table('reports')->limit(2)->pluck('id');
        DB::table('reports')->where('id', $ids[0])->update(['title' => 'ZqxRanking Metal', 'keyword' => 'ZqxRanking Metal', 'pub_date' => '2020-01-01', 'status' => 1]);
        DB::table('reports')->where('id', $ids[1])->update(['title' => 'Other Products By ZqxRanking Metal', 'keyword' => 'Other Products', 'pub_date' => '2026-01-01', 'status' => 1]);
        $this->getJson('/api/autocomplete/search?s=ZqxRanking')->assertOk()->assertJsonPath('data.reports.0.id', $ids[0]);
        $this->get('/searchfor?s=ZqxRanking')->assertOk()->assertViewHas('list', fn ($rows) => $rows[0]->id === $ids[0]);
    }

    public function test_short_queries_and_literal_wildcards_do_not_return_unrelated_reports(): void
    {
        foreach (['', 'x'] as $term) {
            $this->getJson('/api/autocomplete/search?s='.urlencode($term))->assertOk()->assertJsonCount(0, 'data.reports');
        }
        $this->getJson('/api/autocomplete/search?s=%25%25')->assertOk()->assertJsonCount(0, 'data.reports');
        $this->getJson('/api/autocomplete/search?s%5B%5D=meta')->assertUnprocessable();
        $this->getJson('/api/autocomplete/search?s='.str_repeat('a', 121))->assertUnprocessable();
    }
}
