const fs=require('fs'),path=require('path');
const root=path.resolve(__dirname,'..');
const source=path.join(root,'node_modules','tinymce');
const destination=path.join(root,'public','vendor','tinymce');
if(!fs.existsSync(path.join(source,'tinymce.min.js')))throw new Error('TinyMCE is not installed. Run npm ci first.');
fs.mkdirSync(destination,{recursive:true});
for(const item of ['tinymce.min.js','license.md','notices.txt','icons','models','plugins','skins','themes']){
 const target=path.join(source,item);if(fs.existsSync(target))fs.cpSync(target,path.join(destination,item),{recursive:true});
}
console.log('Self-hosted TinyMCE assets installed in public/vendor/tinymce.');
