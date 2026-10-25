(()=>{
 'use strict';
 const overlay=document.getElementById('searchDiv'),trigger=document.getElementById('header-search-toggle'),headerInput=document.getElementById('search'),nav=document.getElementById('mySidenav');
 const mobile=matchMedia('(max-width:991px)');
 const closeHeader=(restoreFocus=false)=>{
  if(!overlay||!trigger)return;
  overlay.style.display='none';overlay.setAttribute('aria-hidden','true');trigger.setAttribute('aria-expanded','false');
  overlay.querySelectorAll('[data-report-search]').forEach(form=>form.dispatchEvent(new Event('dismiss-search')));
  if(restoreFocus){const toggle=document.querySelector('.navbar-toggler');(trigger.getClientRects().length?trigger:toggle)?.focus({preventScroll:true});}
 };
 const openHeader=()=>{
  if(!overlay||!trigger||!headerInput)return;
  if(mobile.matches&&nav&&window.bootstrap)bootstrap.Collapse.getOrCreateInstance(nav,{toggle:false}).hide();
  overlay.style.display='block';overlay.setAttribute('aria-hidden','false');trigger.setAttribute('aria-expanded','true');headerInput.focus({preventScroll:true});
 };
 if(overlay&&trigger){
  overlay.setAttribute('aria-hidden','true');
  trigger.addEventListener('click',()=>trigger.getAttribute('aria-expanded')==='true'?closeHeader(true):openHeader());
  document.getElementById('bottle')?.addEventListener('click',()=>closeHeader(true));
  document.addEventListener('click',event=>{if(trigger.getAttribute('aria-expanded')==='true'&&!overlay.contains(event.target)&&!trigger.contains(event.target))closeHeader();});
  document.addEventListener('keydown',event=>{if(event.key==='Escape'&&trigger.getAttribute('aria-expanded')==='true'){event.preventDefault();closeHeader(true);}});
 }
 // Preserve compatibility with any existing links that open the shared search.
 window.showDiv=openHeader;window.hideDiv=()=>closeHeader(true);
 document.querySelectorAll('form[data-report-search]').forEach((form,index)=>{
  const input=form.querySelector('input[name=s]');if(!input)return;
  const panel=document.createElement('div');panel.className='report-search-panel';panel.hidden=true;
  const status=document.createElement('div');status.className='report-search-status';status.setAttribute('role','status');status.setAttribute('aria-live','polite');
  const list=document.createElement('ul');list.className='report-search-results';list.id='report-search-results-'+index;list.setAttribute('role','listbox');list.setAttribute('aria-label','Matching reports');
  const all=document.createElement('a');all.className='report-search-view-all';all.textContent='View all matching reports';all.hidden=true;
  panel.append(status,list,all);form.append(panel);
  input.setAttribute('role','combobox');input.setAttribute('aria-autocomplete','list');input.setAttribute('aria-controls',list.id);input.setAttribute('aria-expanded','false');input.autocomplete='off';
  let timer,controller,version=0,active=-1,links=[],composing=false;const cache=new Map();
  const hide=()=>{clearTimeout(timer);controller?.abort();version++;panel.hidden=true;form.classList.remove('is-search-open');input.setAttribute('aria-expanded','false');input.removeAttribute('aria-activedescendant');active=-1;};
  const show=()=>{panel.hidden=false;form.classList.add('is-search-open');input.setAttribute('aria-expanded','true');};
  const select=index=>{active=index;links.forEach((link,i)=>link.setAttribute('aria-selected',String(i===active)));if(active>=0){input.setAttribute('aria-activedescendant',links[active].id);links[active].scrollIntoView({block:'nearest'});}else input.removeAttribute('aria-activedescendant');};
  const render=(reports,query)=>{
   list.replaceChildren();active=-1;links=[];input.removeAttribute('aria-activedescendant');
   reports.forEach((report,i)=>{
    const url=new URL(report.rd_link,location.origin);if(url.origin!==location.origin)return;
    const li=document.createElement('li'),link=document.createElement('a'),title=document.createElement('span'),meta=document.createElement('small');
    link.href=url.href;link.id=list.id+'-'+i;link.setAttribute('role','option');link.setAttribute('aria-selected','false');link.tabIndex=-1;
    title.className='report-search-result-title';title.textContent=report.short_title||report.title;
    meta.textContent='Report ID: '+report.id+(report.date_string?'  |  '+report.date_string:'');link.append(title,meta);li.append(link);list.append(li);links.push(link);
    link.addEventListener('mousemove',()=>select(links.indexOf(link)));
   });
   status.textContent=links.length?'Matching reports':'No matching reports. Try another keyword.';
   const url=new URL(form.action,location.origin);url.searchParams.set('s',query);all.href=url.href;all.hidden=!links.length;show();
  };
  const search=()=>{
   if(composing)return;clearTimeout(timer);controller?.abort();const id=++version,query=input.value.trim().replace(/\s+/g,' ');
   list.replaceChildren();links=[];active=-1;all.hidden=true;input.removeAttribute('aria-activedescendant');
   if(query.length<2){hide();return;}
   status.textContent='Searching reports...';show();
   timer=setTimeout(async()=>{
    if(cache.has(query)){if(id===version)render(cache.get(query),query);return;}
    controller=new AbortController();
    try{
     const url=new URL(form.dataset.autocompleteUrl,location.origin);url.searchParams.set('s',query);
     const response=await fetch(url,{signal:controller.signal,headers:{Accept:'application/json'}});if(!response.ok)throw new Error('Search failed');
     const data=await response.json();if(id!==version)return;
     const reports=Array.isArray(data.data?.reports)?data.data.reports:[];if(cache.size>=20)cache.delete(cache.keys().next().value);cache.set(query,reports);render(reports,query);
    }catch(error){if(error.name!=='AbortError'&&id===version){status.textContent='Search suggestions are unavailable. Press Enter to search reports.';show();}}
   },250);
  };
  input.addEventListener('input',search);input.addEventListener('focus',()=>{if(input.value.trim().length>=2)search();});
  input.addEventListener('compositionstart',()=>{composing=true;hide();});input.addEventListener('compositionend',()=>{composing=false;search();});
  input.addEventListener('keydown',event=>{
   if(event.isComposing)return;
   if(event.key==='Escape'){hide();return;}
   if(!panel.hidden&&links.length&&['ArrowDown','ArrowUp'].includes(event.key)){event.preventDefault();select(event.key==='ArrowDown'?(active+1)%links.length:(active<=0?links.length-1:active-1));}
   if(event.key==='Enter'&&active>=0&&!panel.hidden){event.preventDefault();location.assign(links[active].href);}
  });
  form.addEventListener('submit',event=>{input.value=input.value.trim();if(!input.value){event.preventDefault();hide();input.focus();}});
  form.addEventListener('dismiss-search',hide);
  form.addEventListener('focusout',()=>setTimeout(()=>{if(!form.contains(document.activeElement))hide();},0));
  document.addEventListener('click',event=>{if(!form.contains(event.target))hide();});
 });
})();
