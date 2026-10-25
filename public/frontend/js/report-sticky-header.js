(()=>{
 'use strict';
 const bar=document.getElementById('report-sticky-header'),hero=document.querySelector('.report-hero'),header=document.querySelector('.site-header');
 if(!bar||!hero||!header)return;
 let scheduled=false;
 const measure=()=>{if(!bar.hidden)document.documentElement.style.setProperty('--report-sticky-height',Math.ceil(bar.getBoundingClientRect().height)+'px');};
 const update=()=>{
  scheduled=false;
  const active=hero.getBoundingClientRect().bottom<=header.getBoundingClientRect().height;
  // Keep navigation available while focus remains in its controls.
  const show=active&&!header.contains(document.activeElement);
  if(!show&&bar.contains(document.activeElement))return;
  bar.hidden=!show;document.body.classList.toggle('report-sticky-active',show);measure();
 };
 const queue=()=>{if(!scheduled){scheduled=true;requestAnimationFrame(update);}};
 window.addEventListener('scroll',queue,{passive:true});window.addEventListener('resize',queue);
 header.addEventListener('focusout',()=>setTimeout(queue,0));
 bar.addEventListener('focusout',()=>setTimeout(queue,0));
 bar.querySelector('.report-sticky-title').addEventListener('click',event=>{
  const title=document.getElementById('report-page-title');if(!title)return;
  event.preventDefault();title.setAttribute('tabindex','-1');title.focus({preventScroll:true});
  title.scrollIntoView({block:'start',behavior:'instant'});queue();
 });
 new ResizeObserver(measure).observe(bar);new ResizeObserver(queue).observe(hero);
 document.fonts.ready.then(queue);window.addEventListener('load',queue);queue();
})();
