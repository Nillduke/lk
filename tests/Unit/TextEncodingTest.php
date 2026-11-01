<?php
namespace Tests\Unit;
use App\Support\TextEncoding;
use PHPUnit\Framework\TestCase;
class TextEncodingTest extends TestCase
{
 private function broken(string $s):string{return mb_convert_encoding($s,'UTF-8','Windows-1252');}
 public function test_repairs_quotes_dashes_symbols_greek_and_repeated_encoding():void {
  foreach(["\u{2018}we\u{2019}","Company\u{2019}s","2026\u{2013}2035","\u{00ae} \u{00b1} \u{03b1} \u{03b2} \u{03bc}","caf\u{00e9}"] as $s){$this->assertSame($s,TextEncoding::repair($this->broken($s)));$this->assertSame($s,TextEncoding::repair($this->broken($this->broken($s))));}
 }
 public function test_preserves_valid_multilingual_and_html_without_decoding_escaped_markup():void {
  $text="Caf\u{00e9}, S\u{00e3}o Paulo, \u{00c5}ngstr\u{00f6}m, TNF-\u{03b1}, \u{4e2d}\u{6587}, 10 \u{00b5}m &amp; &lt;script&gt; &quot;quoted&quot; <a href='/report/test'>Research</a>";
  $this->assertSame($text,TextEncoding::repair($text));$this->assertSame("Unknown \u{fffd}",TextEncoding::repair("Unknown \u{fffd}"));
 }
 public function test_handles_entity_encoded_corruption_and_json_but_preserves_valid_json_bytes():void {
  $this->assertSame("Company\u{2019}s &amp; research",TextEncoding::repair('Company&acirc;&euro;&trade;s &amp; research'));
  $json=json_encode(['question'=>$this->broken("What\u{2019}s next?"),'answer'=>'<p>&lt;script&gt;</p>']);$fixed=json_decode(TextEncoding::json($json),true);$this->assertSame("What\u{2019}s next?",$fixed['question']);$this->assertSame('<p>&lt;script&gt;</p>',$fixed['answer']);
  $valid='{ "title": "Research", "values": [] }';$this->assertSame($valid,TextEncoding::json($valid));
 }
 public function test_recovers_lost_closing_quote_only_with_an_intact_opening_quote():void {
  $this->assertSame("<p>A \u{201c}No Return\u{201d} policy.</p>",TextEncoding::repair("<p>A \u{00e2}\u{20ac}\u{0153}No Return\u{00e2}\u{20ac}\u{fffd} policy.</p>"));
  $unknown="Unknown \u{00e2}\u{20ac}\u{fffd}";$this->assertSame($unknown,TextEncoding::repair($unknown));
 }

 public function test_encoding_repair_never_unescapes_adjacent_html_entities():void {
  $this->assertSame("\u{2019}&lt;script&gt;&amp;",TextEncoding::repair('&acirc;&euro;&trade;&lt;script&gt;&amp;'));
  $this->assertSame("&lt;\u{00e9}&gt;",TextEncoding::repair('&lt;&#195;&#169;&gt;'));
 }

}
