<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportImportTest extends TestCase
{
    use DatabaseTransactions;
    private string $prefix;
    protected function setUp(): void { parent::setUp(); $this->prefix='import-fix-'.Str::lower(Str::random(10)); $this->actingAs(User::where('username','lk_admin')->firstOrFail()); }
    protected function tearDown(): void
    {
        foreach (DB::table('reports')->where('slug','like',$this->prefix.'%')->pluck('id') as $id) {
            $dir=public_path('uploads/images/reports/'.(int)$id);
            foreach (glob($dir.'/*.svg') ?: [] as $file) unlink($file);
            if (is_dir($dir) && count(scandir($dir))===2) rmdir($dir);
        }
        parent::tearDown();
    }
    private function base(string $suffix=''): array
    {
        $source=DB::table('reports')->first();
        return ['title'=>'Global Import Verification Market Analysis','slug'=>$this->prefix.$suffix,'category_id'=>$source->category_id,'publisher_id'=>$source->publisher_id,'keyword'=>'Import Verification','types'=>'Type A, Type B','applications'=>'Use A, Use B','key_players'=>'Company A, Company B','single_user_price'=>1234];
    }
    private function csv(array $rows, array $options=[]): array
    {
        $f=fopen('php://temp','w+');fputcsv($f,array_keys($rows[0]));foreach($rows as $row)fputcsv($f,array_values($row));rewind($f);$content=stream_get_contents($f);fclose($f);
        return $this->postJson('/authuser/reports/import',$options+['file'=>UploadedFile::fake()->createWithContent('reports.csv',$content)])->assertOk()->json('data');
    }
    private function meta(string $suffix=''): object { return DB::table('reports_meta')->where('report_id',DB::table('reports')->where('slug',$this->prefix.$suffix)->value('id'))->first(); }
    public function test_blank_content_generates_legacy_template_svg_and_toc(): void
    {
        $row=$this->base();$r=$this->csv([$row]);$this->assertSame(1,$r['created']);$this->assertSame(0,$r['failed']);
        $meta=$this->meta();$this->assertStringContainsString('Type A',$meta->content);$this->assertStringContainsString('Company A',$meta->toc);
        preg_match('~src="(/uploads/images/reports/[^\"]+\.svg)"~',$meta->content,$m);$this->assertNotEmpty($m);
        $svg=file_get_contents(public_path($m[1]));$doc=new \DOMDocument();$this->assertTrue($doc->loadXML($svg));$this->assertStringContainsString('Import Verification',$svg);$this->assertStringContainsString('Type A',$svg);
        $this->get($m[1])->assertOk()->assertHeader('Content-Type','image/svg+xml');
        $this->get('/report/'.$row['slug'])->assertOk()->assertSee('Import Verification')->assertSee('Type A')->assertSee('Company A');
    }
    public function test_generated_content_can_have_no_images_and_healthcare_uses_its_template(): void
    {
        $row=$this->base('-no-images');$row['include_images']=0;$row['category_id']=15;$r=$this->csv([$row]);$this->assertSame(1,$r['created']);
        $meta=$this->meta('-no-images');$this->assertStringNotContainsString('<img',$meta->content);$this->assertStringContainsString('Key Research Highlights: Market Drivers',$meta->content);$this->assertNotEmpty($meta->toc);
        $this->assertEmpty(glob(public_path('uploads/images/reports/'.$meta->report_id.'/*.svg')));
    }
    public function test_supplied_content_is_preserved_and_custom_image_is_inserted_without_duplicates(): void
    {
        $row=$this->base('-custom');$row['content']="<h2>Exact custom HTML</h2>\n<p>Do not replace this.</p>";$row['toc']='<p>Exact TOC</p>';
        $this->assertSame(1,$this->csv([$row])['created']);$this->assertSame($row['content'],$this->meta('-custom')->content);$this->assertSame($row['toc'],$this->meta('-custom')->toc);
        $id=$this->meta('-custom')->report_id;
        $image=['id'=>$id,'image_url'=>'/frontend/images/eternity_logo.png'];
        $this->assertSame(1,$this->csv([$image],['update_existing'=>1])['updated']);$this->assertSame(1,$this->csv([$image],['update_existing'=>1])['updated']);
        $meta=$this->meta('-custom');$this->assertSame(1,substr_count($meta->content,'<img'));$this->assertStringContainsString('Do not replace this.',$meta->content);$this->assertSame('<p>Exact TOC</p>',$meta->toc);
        $generated=$this->base('-url');$generated['image_url']='/frontend/images/eternity_logo.png';$this->assertSame(1,$this->csv([$generated])['created']);$this->assertStringContainsString('src="/frontend/images/eternity_logo.png"',$this->meta('-url')->content);$this->assertEmpty(glob(public_path('uploads/images/reports/'.$this->meta('-url')->report_id.'/*.svg')));
    }
    public function test_blank_and_omitted_update_cells_preserve_all_existing_values(): void
    {
        $row=$this->base('-preserve')+['pages'=>123,'region'=>'Europe','status'=>0,'pub_date'=>'2020-02-03','content'=>'<p>Saved content with image</p><img src="/frontend/images/eternity_logo.png">','toc'=>'Saved TOC','tables_figures'=>'Saved figures'];
        $this->assertSame(1,$this->csv([$row])['created']);$before=DB::table('reports')->where('slug',$row['slug'])->first();$metaBefore=$this->meta('-preserve');
        $update=['id'=>$before->id,'title'=>'','category_id'=>'','publisher_id'=>'','pages'=>'','region'=>'','status'=>'','pub_date'=>'','content'=>'','toc'=>'','tables_figures'=>'','types'=>'','single_user_price'=>''];
        $this->assertSame(1,$this->csv([$update],['update_existing'=>1,'category_id'=>15,'publisher_id'=>1])['updated']);
        $after=DB::table('reports')->where('id',$before->id)->first();foreach(['title','category_id','publisher_id','pages','region','status','pub_date','key_info'] as $field)$this->assertSame($before->$field,$after->$field,$field);
        $metaAfter=$this->meta('-preserve');foreach(['content','toc','tables_figures','created_at'] as $field)$this->assertSame($metaBefore->$field,$metaAfter->$field);
        $this->assertDatabaseHas('reports_licenses',['report_id'=>$before->id,'lic_price'=>1234]);
        $this->csv([['id'=>$before->id,'pages'=>0,'status'=>1,'types'=>'Changed type','single_user_price'=>0]],['update_existing'=>1]);
        $after=DB::table('reports')->where('id',$before->id)->first();$segments=json_decode($after->key_info,true);$this->assertSame('Changed type',$segments['types']);$this->assertSame('Use A, Use B',$segments['applications']);$this->assertSame(0,$after->pages);$this->assertSame(1,$after->status);$this->assertDatabaseHas('reports_licenses',['report_id'=>$before->id,'lic_price'=>0]);
    }
    public function test_existing_reports_skip_by_default_and_regeneration_requires_explicit_choice(): void
    {
        $row=$this->base('-regen')+['content'=>'Handwritten content','toc'=>'Handwritten TOC'];$this->csv([$row]);$id=$this->meta('-regen')->report_id;
        $update=['id'=>$id,'title'=>'Updated report title','types'=>'New segment','regenerate_content'=>1];$this->assertSame(1,$this->csv([$update])['skipped']);$this->assertSame('Handwritten content',$this->meta('-regen')->content);
        $this->assertSame(1,$this->csv([$update],['update_existing'=>1])['updated']);$meta=$this->meta('-regen');$this->assertStringContainsString('New segment',$meta->content);$this->assertStringNotContainsString('Handwritten content',$meta->content);$this->assertStringContainsString('New segment',$meta->toc);
        $this->csv([['id'=>$id,'regenerate_content'=>1,'content'=>'Supplied replacement','toc'=>'Supplied TOC']],['update_existing'=>1]);$this->assertSame('Supplied replacement',$this->meta('-regen')->content);$this->assertSame('Supplied TOC',$this->meta('-regen')->toc);
    }
    public function test_blank_slug_defaults_names_zero_prices_and_legacy_id_matching(): void
    {
        $row=$this->base('-slug');$row['title']=$this->prefix.' Auto Slug Market';$row['slug']='';$row['category_id']=DB::table('categories')->where('id',$row['category_id'])->value('name');$row['publisher_id']=DB::table('publishers')->where('id',$row['publisher_id'])->value('name');$row['include_images']=0;
        $this->assertSame(1,$this->csv([$row])['created']);$id=DB::table('reports')->where('slug',Str::slug($row['title']))->value('id');$this->assertNotNull($id);
        $this->assertSame(1,$this->csv([['slug'=>$id,'pages'=>45]],['update_existing'=>1])['updated']);$this->assertDatabaseHas('reports',['id'=>$id,'pages'=>45]);
    }
    public function test_invalid_rows_do_not_partially_change_reports_and_duplicates_are_rejected(): void
    {
        $row=$this->base('-invalid')+['content'=>'Valid original'];$this->csv([$row]);$id=$this->meta('-invalid')->report_id;
        foreach ([['id'=>$id,'image_url'=>'javascript:alert(1)'],['id'=>$id,'image_url'=>'/uploads/missing-image.png'],['id'=>$id,'single_user_price'=>'12.75'],['id'=>$id,'include_images'=>0,'image_url'=>'/frontend/images/eternity_logo.png']] as $bad) {
            $r=$this->csv([$bad],['update_existing'=>1]);$this->assertSame(1,$r['failed']);$this->assertSame(0,$r['updated']);$this->assertSame('Valid original',$this->meta('-invalid')->content);
        }
        $file=UploadedFile::fake()->createWithContent('duplicate.csv',"title,report_title\nA,B\n");$this->postJson('/authuser/reports/import',['file'=>$file])->assertUnprocessable();
    }
    public function test_corporate_license_updates_preserve_ids_and_generated_xml_escapes_input(): void
    {
        $row=$this->base('-escaping');$row['keyword']='Research & <Markets>';$row['types']='A & B, <img src=x onerror=alert(1)>';$this->csv([$row]);$meta=$this->meta('-escaping');
        preg_match('~src="(/uploads/images/reports/[^\"]+\.svg)"~',$meta->content,$m);$doc=new \DOMDocument();$this->assertTrue($doc->loadXML(file_get_contents(public_path($m[1]))));$this->assertStringNotContainsString('<img src=x',$meta->toc);
        $license=DB::table('reports_licenses')->insertGetId(['report_id'=>$meta->report_id,'lic_name'=>'Corporate User License','lic_price'=>5000]);
        $this->csv([['id'=>$meta->report_id,'corporate_price'=>6000]],['update_existing'=>1]);$this->assertDatabaseHas('reports_licenses',['id'=>$license,'lic_price'=>6000]);$this->assertSame(1,DB::table('reports_licenses')->where('report_id',$meta->report_id)->whereIn('lic_name',['Corporate License','Corporate User License'])->count());
    }
}
