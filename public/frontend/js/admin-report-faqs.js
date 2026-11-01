(()=>{
'use strict';
const panel=document.querySelector('#report-faq-editor');if(!panel)return;
const form=panel.closest('form'),list=panel.querySelector('[data-faq-rows]'),mode=panel.querySelector('[data-faq-mode]'),feedback=panel.querySelector('[data-faq-feedback]');
const rows=()=>[...list.querySelectorAll('[data-faq-row]')];
function status(message,error=false){feedback.textContent=message;feedback.className=error?'small text-danger':'small text-muted';}
function renumber(){const all=rows();all.forEach((row,i)=>{row.querySelector('[data-faq-number]').textContent='FAQ '+(i+1);['question','answer'].forEach(field=>{const input=row.querySelector('[data-faq-'+field+']');input.name=`report_faqs[${i}][${field}]`;input.id=`faq-${field}-${i}`;row.querySelector(`label[for^="faq-${field}-"]`).htmlFor=input.id;});row.querySelector('[data-faq-up]').disabled=i===0;row.querySelector('[data-faq-down]').disabled=i===all.length-1;});panel.querySelector('[data-faq-add]').disabled=all.length>=20;}
function add(data={question:'',answer:''}){const fragment=panel.querySelector('[data-faq-template]').content.cloneNode(true);fragment.querySelector('[data-faq-question]').value=data.question||'';fragment.querySelector('[data-faq-answer]').value=data.answer||'';fragment.querySelector('[data-faq-row-note]').textContent=data.answer?'Review this answer before publishing.':'No supported answer extracted. Add an answer to publish this question.';list.append(fragment);renumber();}
panel.addEventListener('input',event=>{if(event.target.matches('[data-faq-question],[data-faq-answer]')){mode.value='custom';status('Custom FAQs will be published when you save this report.');}});
panel.addEventListener('click',event=>{const button=event.target.closest('button');if(!button)return;const row=button.closest('[data-faq-row]');if(button.matches('[data-faq-add]')){if(rows().length<20){add();mode.value='custom';rows().at(-1).querySelector('[data-faq-question]').focus();}}else if(row&&button.matches('[data-faq-remove]')){row.remove();mode.value='custom';renumber();}else if(row&&button.matches('[data-faq-up]')&&row.previousElementSibling){list.insertBefore(row,row.previousElementSibling);mode.value='custom';renumber();}else if(row&&button.matches('[data-faq-down]')&&row.nextElementSibling){list.insertBefore(row.nextElementSibling,row);mode.value='custom';renumber();}});
panel.querySelector('[data-faq-extract]').addEventListener('click',async event=>{
 const button=event.currentTarget;button.disabled=true;status('Extracting supported answers from the current report...');
 try{
  const segments=[...form.querySelectorAll('.report-segment-row')].map(row=>{const get=field=>row.querySelector(`[name$="[${field}]"]`)?.value||'';return {key:get('key'),label:get('label'),value:get('value')};});
  const content=window.tinymce?.get('report_content')?.getContent()??form.elements.namedItem('content')?.value??'';
  const payload={title:form.elements.namedItem('title')?.value||'',keyword:form.elements.namedItem('keyword')?.value||'',content,report_segments:segments};
  const response=await fetch(panel.dataset.extractUrl,{method:'POST',credentials:'same-origin',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':form.querySelector('[name="_token"]').value},body:JSON.stringify(payload)});
  const data=await response.json();if(!response.ok)throw new Error(Object.values(data.errors||{}).flat()[0]||data.message||'Extraction failed.');
  if(!Array.isArray(data.rows))throw new Error('Could not read extracted FAQs.');
  list.replaceChildren();data.rows.forEach(add);mode.value='custom';const count=data.rows.filter(row=>row.answer?.trim()).length;
  status(`${count} supported answers extracted. Review them and save to publish. Questions without answers stay hidden.`);
 }catch(error){status(error.message||'Could not extract FAQs. Your existing drafts have been kept.',true);}finally{button.disabled=false;}
});
renumber();
})();
