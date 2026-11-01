(()=>{
'use strict';
function normalize(value){
 for(let i=0;i<2;i++){
  const element=document.createElement('textarea');element.innerHTML=value;value=element.value;
  try{value=decodeURIComponent(value);}catch{}
 }
 return value.normalize('NFKC').replace(/\p{Cf}/gu,'').replace(/\[(?:\.|dot)\]|\((?:\.|dot)\)/gi,'.');
}
function hasLink(value,email){
 const text=normalize(value);
 if(/\b(?:https?|hxxps?|ftp|mailto|tel|javascript)\s*:|\bdata\s*:\s*[\w.+-]+\/[\w.+-]+[;,]|www\s*\.|<\s*a\b|\b(?:href|src)\s*=|\[\/?url(?:[=\]])|\[[^\]\r\n]*\]\([^\r\n)]*\)/iu.test(text))return true;
 if(email)return false;
 return /(?:[\p{L}\p{N}](?:[\p{L}\p{N}-]{0,62})\.)+(?:[\p{L}]{2,63}|xn--[a-z0-9-]{2,59})(?![\p{L}\p{N}_-])/iu.test(text)||/(?:^|\s)\/\/[a-z0-9]/i.test(text);
}
function validPhone(value){
 const text=value.trim(),main=text.replace(/\s*(?:ext\.?|x|#)\s*\d{1,8}$/i,''),digits=main.replace(/\D/g,'');
 return /^\+?[0-9 ()\-.]+(?:\s*(?:ext\.?|x|#)\s*\d{1,8})?$/i.test(text)&&digits.length>=7&&digits.length<=15;
}
document.querySelectorAll('form[data-enquiry-form]').forEach(form=>{
 const fields=[...form.querySelectorAll('[name="full_name"],[name="email_id"],[name="contact_no"],[name="message"],[name="company"],[name="role"],[name="country"],[name="city"]')];
 const limits={full_name:100,email_id:100,contact_no:40,message:500,company:50,role:50,city:100};
 fields.forEach(field=>{if(limits[field.name])field.maxLength=limits[field.name];if(field.name==='full_name')field.minLength=2;if(field.name==='email_id')field.type='email';});
 const error=form.querySelector('.enquiry-security-error');
 const setError=message=>{if(error){error.textContent=message;error.hidden=!message;}};
 function check(field){
  field.setCustomValidity('');
  if(field.value&&hasLink(field.value,field.name==='email_id'))field.setCustomValidity('Links and website addresses are not allowed in this field.');
  else if(field.name==='contact_no'&&field.value&&!validPhone(field.value))field.setCustomValidity('Enter a valid phone number with 7 to 15 digits.');
 }
 fields.forEach(field=>{field.addEventListener('input',()=>{check(field);setError('');});field.addEventListener('change',()=>check(field));});
 const widget=form.querySelector('.cf-turnstile');
 if(widget&&widget.parentElement.clientWidth<300){widget.dataset.size='compact';widget.style.minHeight='140px';}
 form.addEventListener('submit',event=>{
  fields.forEach(check);setError('');
  if(!form.checkValidity()){event.preventDefault();event.stopImmediatePropagation();form.reportValidity();return;}
  if(widget&&!form.querySelector('[name="cf-turnstile-response"]')?.value){event.preventDefault();event.stopImmediatePropagation();setError('Please complete the security verification. If it is not loading, refresh this page.');error?.focus();}
 },true);
});
})();
