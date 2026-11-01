<?php
namespace Tests\Unit;
use Tests\TestCase;
class ReportDisplayContentTest extends TestCase
{
    public function test_only_style_attributes_are_removed_regardless_of_attribute_order_and_quote_type(): void
    {
        $html=<<<'HTML'
<p style="font-family:'Arial';color:red">Report text.</p>
<img style="width:800px;height:500px" src="/uploads/images/a.png" alt="A" width="800">
<img src='/uploads/images/b.png' style='display:block' alt='B' data-note="literal style='keep this'">
<img STYLE = display:block src="/uploads/images/c.png" srcset="/uploads/images/c-small.png 400w" alt="A > B">
<a style="color:red" href="/request-a-sample/1" title="Request style='literal'">Sample</a>
<script>const html = '<img style="display:none" src="keep.png">';</script>
<!-- <img style="color:red" src="comment.png"> -->
HTML;
        $out=report_display_content($html);
        foreach(['/uploads/images/a.png','/uploads/images/b.png','/uploads/images/c.png','srcset=','alt="A > B"','width="800"','href="/request-a-sample/1"',"data-note=\"literal style='keep this'\"","title=\"Request style='literal'\""] as $attribute)$this->assertStringContainsString($attribute,$out);
        $this->assertStringNotContainsString('height:500px',$out);$this->assertStringNotContainsString('STYLE = display:block',$out);$this->assertStringNotContainsString('font-family',$out);
        $this->assertStringContainsString('<p>Report text.</p>',$out);
        $this->assertStringContainsString('<script>const html = \'<img style="display:none" src="keep.png">\';</script>',$out);
        $this->assertStringContainsString('<!-- <img style="color:red" src="comment.png"> -->',$out);
    }
}
