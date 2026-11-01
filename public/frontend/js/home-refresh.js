(()=>{
'use strict';
const home=document.querySelector('.ei-home');if(!home)return;
const tabs=[...home.querySelectorAll('[data-home-tab]')],panels=[...home.querySelectorAll('[data-home-panel]')];
function activate(tab,focus=false){tabs.forEach(button=>{const active=button===tab;button.setAttribute('aria-selected',String(active));button.tabIndex=active?0:-1;});panels.forEach(panel=>panel.hidden=panel.dataset.homePanel!==tab.dataset.homeTab);if(focus)tab.focus();}
tabs.forEach((tab,i)=>{tab.addEventListener('click',()=>activate(tab));tab.addEventListener('keydown',event=>{let index;if(event.key==='ArrowRight')index=(i+1)%tabs.length;else if(event.key==='ArrowLeft')index=(i+tabs.length-1)%tabs.length;else if(event.key==='Home')index=0;else if(event.key==='End')index=tabs.length-1;else return;event.preventDefault();activate(tabs[index],true);});});
home.querySelectorAll('[data-home-news-image]').forEach(img=>{const fallback=()=>{img.hidden=true;img.parentElement.classList.add('has-fallback');};img.addEventListener('error',fallback);if(img.complete&&!img.naturalWidth)fallback();});
const quotes=[...home.querySelectorAll('[data-home-quote]')],count=home.querySelector('[data-home-quote-count]');let current=0;
function show(index){current=(index+quotes.length)%quotes.length;quotes.forEach((quote,i)=>quote.hidden=i!==current);count.textContent=String(current+1).padStart(2,'0')+' / '+String(quotes.length).padStart(2,'0');}
home.querySelector('[data-home-quote-prev]')?.addEventListener('click',()=>show(current-1));home.querySelector('[data-home-quote-next]')?.addEventListener('click',()=>show(current+1));
})();
