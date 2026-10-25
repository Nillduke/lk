/* Keep mobile navigation within the available viewport. */
(()=>{
 const root=document.documentElement,media=matchMedia('(max-width:991px)');
 const update=()=>{
  if(!media.matches){['--mobile-viewport-height','--mobile-header-height'].forEach(name=>root.style.removeProperty(name));return;}
  const utility=document.querySelector('.site-header .header.bg'),nav=document.querySelector('.site-header .custom-nav');
  const headerHeight=(utility?utility.offsetHeight:0)+(nav?parseFloat(getComputedStyle(nav).minHeight)||67:67);
  root.style.setProperty('--mobile-header-height',headerHeight+'px');root.style.setProperty('--mobile-viewport-height',(window.visualViewport?visualViewport.height:innerHeight)+'px');
 };
 media.addEventListener('change',update);window.addEventListener('resize',update);window.visualViewport?.addEventListener('resize',update);update();
 document.querySelectorAll('.interior-table-wrap').forEach((wrapper,i)=>{wrapper.tabIndex=0;wrapper.setAttribute('role','region');wrapper.setAttribute('aria-label',wrapper.closest('.billing-wraper')?'Shopping cart details':'Scrollable data table '+(i+1));});
})();
// Reserve enough hero height for the longest slide and the mobile search controls.
(()=>{
 const hero=document.querySelector('.page-home #demo'),media=matchMedia('(max-width:767px)');if(!hero)return;
 const discovery=hero.querySelector('.home-discovery');if(!discovery)return;
 let previous='';
 const fit=()=>{
  if(!media.matches){hero.style.removeProperty('--mobile-hero-height');previous='';return;}
  const active=hero.querySelector('.carousel-item.active .carousel-caption');if(!active)return;
  const width=active.getBoundingClientRect().width;if(!width)return;
  let tallest=0;
  hero.querySelectorAll('.carousel-item .carousel-caption').forEach(caption=>{const clone=caption.cloneNode(true);clone.setAttribute('aria-hidden','true');clone.style.cssText='visibility:hidden!important;pointer-events:none!important;position:absolute!important;top:0!important;bottom:auto!important;left:0!important;right:auto!important;max-width:none!important;width:'+width+'px!important;';hero.append(clone);tallest=Math.max(tallest,clone.getBoundingClientRect().height);clone.remove();});
  const top=parseFloat(getComputedStyle(active).top)||45,bottom=parseFloat(getComputedStyle(discovery).bottom)||56;
  const height=Math.max(610,Math.ceil(top+tallest+28+discovery.getBoundingClientRect().height+bottom));
  if(previous!==String(height)){hero.style.setProperty('--mobile-hero-height',height+'px');previous=String(height);}
 };
 requestAnimationFrame(fit);window.addEventListener('load',fit);window.addEventListener('resize',fit);media.addEventListener('change',fit);document.fonts.ready.then(fit);new ResizeObserver(fit).observe(discovery);
})();
