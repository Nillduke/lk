<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReportFaqs
{
    public function text(string $value): string
    {
        return trim(preg_replace('/\s+/u',' ',html_entity_decode(strip_tags($value),ENT_QUOTES|ENT_HTML5,'UTF-8')));
    }
    public function market(object $report): string
    {
        $name=$this->text($report->keyword ?? '');
        if ($name==='') $name=preg_split('/\bMarket\b/i',$this->text($report->title ?? ''),2)[0];
        return preg_match('/\bmarket$/i',$name) ? $name : trim($name.' Market');
    }
    public function document(string $html): \DOMDocument
    {
        $doc=new \DOMDocument('1.0','UTF-8');$previous=libxml_use_internal_errors(true);
        try {$doc->loadHTML('<?xml encoding="UTF-8"><main>'.mb_substr($html,0,2000000).'</main>',LIBXML_NONET|LIBXML_NOERROR|LIBXML_NOWARNING);}
        finally {libxml_clear_errors();libxml_use_internal_errors($previous);}
        foreach (['script','style','noscript'] as $tag) foreach(iterator_to_array($doc->getElementsByTagName($tag)) as $node) $node->parentNode->removeChild($node);
        return $doc;
    }
    private function sentences(\DOMDocument $doc): array
    {
        $out=[];
        foreach($doc->getElementsByTagName('p') as $p) {
            $text=$this->text($p->textContent);
            foreach(preg_split('/(?<=[.!?])\s+(?=[\p{Lu}\p{N}])/u',$text) as $s) {
                if (mb_strlen($s)>=35 && mb_strlen($s)<=1800 && !preg_match('/\b(?:XX(?:\.XX)?|\[Market Name\]|COVID-?19)\b/i',$s)) $out[]=$s;
            }
        }
        return array_values(array_unique($out));
    }
    private function selected(array $sentences, callable $match, int $max=1500): string
    {
        $answer='';$count=0;
        foreach($sentences as $sentence) if ($match($sentence) && mb_strlen($answer.' '.$sentence)<=$max) {
            $answer.=($answer===''?'':' ').$sentence;if(++$count===3)break;
        }
        return $answer;
    }
    private function listAfter(\DOMElement $heading): array
    {
        for($node=$heading->nextSibling;$node;$node=$node->nextSibling) {
            if (!$node instanceof \DOMElement) continue;
            if (preg_match('/^h[1-6]$/i',$node->tagName)) break;
            if (in_array(strtolower($node->tagName),['ul','ol'])) {
                $rows=[];foreach($node->getElementsByTagName('li') as $li) if($value=$this->text($li->textContent)) $rows[]=$value;
                return $rows;
            }
        }
        return [];
    }
    public function generate(object $report): array
    {
        $market=$this->market($report);$doc=$this->document($report->content ?? '');$sentences=$this->sentences($doc);
        $size=$this->selected($sentences,fn($s)=>preg_match('/(?:USD|US\s*\$|\$)\s*[0-9][0-9,.]*\s*(?:million|billion|trillion)\b/i',$s) && preg_match('/\bmarket\b/i',$s) && preg_match('/\b(?:size|valued|worth|reach|projected|forecast)\b/i',$s));
        if ($size!=='') {
            $growth=$this->selected($sentences,fn($s)=>preg_match('/\bCAGR\b.{0,80}\b[0-9]+(?:\.[0-9]+)?\s*%/i',$s) && !str_contains($size,$s),500);
            if($growth!=='' && mb_strlen($size.' '.$growth)<=2000)$size.=' '.$growth;
        }
        $trends=$this->selected($sentences,fn($s)=>preg_match('/\b(?:driven by|growth is fueled by|key trends? (?:include|are)|major trends? (?:include|are)|rising demand for|key challenges? (?:include|are)|main restraints? (?:include|are)|opportunities (?:include|arise))\b/i',$s) && !preg_match('/\b(?:report (?:covers|includes|provides)|will be covered|analysis of)\b/i',$s));
        $groups=[];$companies=[];
        foreach($doc->getElementsByTagName('*') as $heading) {
            if(!preg_match('/^h[2-4]$/i',$heading->tagName))continue;
            $text=$this->text($heading->textContent);
            if(preg_match('/\bMarket\s*,?\s*By\s+(.+)$/i',$text,$m)) {
                $values=$this->listAfter($heading);
                if($values) $groups[]=trim($m[1]," :").' ('.implode(', ',array_slice($values,0,12)).')';
            }
            if(!$companies && preg_match('/(?:companies (?:profiled|covered)|key (?:companies|players)|leading (?:companies|players))/i',$text)) $companies=$this->listAfter($heading);
        }
        $key=json_decode($report->key_info ?? '',true);if(!is_array($key))$key=[];
        if(!$groups) foreach($key as $name=>$value) {
            if(in_array(strtolower((string)$name),['key_players','companies','key_companies','players']))continue;
            $value=$this->text(app(ReportOptions::class)->value($value));
            if($value!=='' && !preg_match('/\bXX\b/i',$value))$groups[]=Str::title(str_replace(['_','-'],' ',(string)$name)).' ('.$value.')';
        }
        $segments=$groups ? 'The report segments the '.$market.' by '.implode('; ',array_slice($groups,0,8)).'.' : '';
        if(mb_strlen($segments)>2800)$segments='The report segments the '.$market.' by '.implode(', ',array_map(fn($s)=>strtok($s,'('),array_slice($groups,0,8))).'.';
        $regions=$this->selected($sentences,fn($s)=>preg_match('/\b(?:North America|Europe|Asia(?: Pacific)?|Latin America|South America|Middle East|Africa|China|India|United States)\b/i',$s) && preg_match('/\b(?:dominates?|largest|leads?|leading region|fastest[- ]growing|highest growth|grow at (?:a )?significantly high)\b/i',$s));
        if($regions!=='' && !preg_match('/fastest|highest growth|most rapidly|quickest/i',$regions))$regions.=' The available summary does not identify the fastest-growing region.';
        $companies=array_values(array_filter($companies,fn($v)=>!preg_match('/other (?:players|companies)|can be added|specific interest/i',$v)));
        $players=$companies ? implode('; ',array_slice($companies,0,5)) : $this->text(app(ReportOptions::class)->value($key['key_players'] ?? $key['key_companies'] ?? $key['companies'] ?? ''));
        if(preg_match('/\bXX\b|\[Company /i',$players))$players='';
        if(mb_strlen($players)>2500)$players='';
        $answers=[$size,$trends,$segments,$regions,$players!==''?'Companies profiled in the report include '.$players.(preg_match('/[.!?]$/u',$players)?'':'.'):''];
        $questions=[
            'What is the size of the '.$market.', and how fast is it growing?',
            'What are the main trends and growth drivers in the '.$market.'?',
            'How is the '.$market.' divided into segments?',
            'Which region leads the '.$market.', and which region is growing the fastest?',
            'Who are the top companies in the '.$market.'?',
        ];
        return array_map(fn($i)=>['question'=>$questions[$i],'answer'=>$answers[$i]],range(0,4));
    }
    public function editor(?object $report): array
    {
        $saved=$report ? DB::table('lr_report_faqs')->where('report_id',$report->id)->first() : null;
        return ['mode'=>$saved->mode ?? 'auto','rows'=>$saved && in_array($saved->mode,['custom','hidden']) ? (json_decode($saved->faqs,true) ?: []) : $this->generate($report ?? (object)['keyword'=>'[Market Name]'])];
    }
    public function resolve(object $report): array
    {
        $saved=DB::table('lr_report_faqs')->where('report_id',$report->id)->first();
        if($saved && $saved->mode==='hidden')return [];
        $rows=$saved && $saved->mode==='custom' ? (json_decode($saved->faqs,true) ?: []) : $this->generate($report);
        return array_values(array_filter($rows,fn($r)=>is_array($r) && trim($r['question']??'')!=='' && trim($r['answer']??'')!==''));
    }
    public function validate(Request $request): ?array
    {
        if(!$request->has('report_faqs_present'))return null;
        $data=$request->validate(['faq_mode'=>'required|in:auto,custom,hidden','report_faqs'=>'nullable|array|max:20','report_faqs.*'=>'array','report_faqs.*.question'=>'nullable|string|max:400','report_faqs.*.answer'=>'nullable|string|max:3000']);
        $rows=[];
        foreach($data['report_faqs'] ?? [] as $i=>$row) {
            $q=trim($row['question']??'');$a=trim($row['answer']??'');
            if($a!=='' && $q==='')throw ValidationException::withMessages(['report_faqs.'.$i.'.question'=>'Enter a question for this answer.']);
            if($q!=='' || $a!=='')$rows[]=['question'=>$q,'answer'=>$a];
        }
        if($data['faq_mode']==='custom' && !array_filter($rows,fn($r)=>$r['question']!=='' && $r['answer']!==''))throw ValidationException::withMessages(['report_faqs'=>'Add at least one question and answer, or choose Hide FAQs.']);
        return ['mode'=>$data['faq_mode'],'faqs'=>$rows];
    }
    public function save(int $id, array $data): void
    {
        if($data['mode']==='auto'){DB::table('lr_report_faqs')->where('report_id',$id)->delete();return;}
        $existing=DB::table('lr_report_faqs')->where('report_id',$id)->first();
        DB::table('lr_report_faqs')->updateOrInsert(['report_id'=>$id],['mode'=>$data['mode'],'faqs'=>json_encode($data['faqs'],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),'created_at'=>$existing->created_at ?? now(),'updated_at'=>now()]);
    }
    public function schema(array $rows): string
    {
        return json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>array_map(fn($r)=>['@type'=>'Question','name'=>$r['question'],'acceptedAnswer'=>['@type'=>'Answer','text'=>$r['answer']]],$rows)],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
    }
}
