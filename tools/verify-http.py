from pathlib import Path
import urllib.request,urllib.error,urllib.parse,http.cookiejar,re,json
root=Path(__file__).resolve().parents[1];base='http://localhost:8098';results=[]
jar=http.cookiejar.CookieJar();client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
def check(path,data=None,expected=200):
 try:
  with client.open(base+path,data=urllib.parse.urlencode(data).encode() if data is not None else None,timeout=45) as response:status=response.status;body=response.read().decode(errors='replace');url=response.url
 except urllib.error.HTTPError as e:status=e.code;body=e.read().decode(errors='replace');url=e.url
 result={'path':path,'status':status,'expected':expected,'url':url,'bytes':len(body)};results.append(result);print(path,status,flush=True)
 return body,result
home,_=check('/')
for path in ['/amp','/aboutus','/services','/faqs','/how-to-order','/disclaimer','/terms-and-conditions','/refund-policy','/privacy-policy','/contactus','/html-sitemap','/all-reports','/all-reports?selectPrice=2000-4000','/searchfor?s=solar','/industry/healthcare','/industry-news','/unsubscribe?e=qa@example.test','/api/autocomplete/search?s=solar']:
 check(path)
report=re.search(r'href=[\"\'](?:http://localhost:8098)?(/report/(?!amp/)[^\"\']+)',home)
if report:
 body,_=check(report[1]);rid=re.search(r'name="report_id" value="(\d+)"',body)
 if rid:
  for prefix in ['request-a-sample','ask-for-discount','enquiry-before-buying','request-for-customization','checkout-form']:check('/'+prefix+'/'+rid[1])
login,_=check('/auth/v/login');token=re.search(r'name="_token" value="([^\"]+)"',login)
admin=json.loads((root/'storage/app/local-admin.json').read_text())
body,result=check('/auth/v/login',{'_token':token[1],'username':admin['username'],'password':admin['password']})
if '/authuser/dashboard' not in result['url']:results.append({'path':'admin-login-redirect','status':0,'expected':200})
for resource in ['reports','categories','publishers','news','users','enquiries','checkouts','unsubscribed-users','comments','bugs','pages','roles','permissions']:
 check('/authuser/manage/'+resource)
check('/authuser/reports/import')
check('/frontend/css/style.css');check('/.env',expected=404);check('/app/Http/Controllers/SiteController.php',expected=404)
(root/'storage/app/http-verification.json').write_text(json.dumps(results,indent=2))
failures=[r for r in results if r['status']!=r['expected']];print('FAILED',json.dumps(failures),flush=True)
raise SystemExit(bool(failures))
