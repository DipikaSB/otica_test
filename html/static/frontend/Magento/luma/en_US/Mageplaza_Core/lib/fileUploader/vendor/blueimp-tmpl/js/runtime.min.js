;(function($){'use strict'
var tmpl=function(id,data){var f=tmpl.cache[id]
return data?f(data,tmpl):function(data){return f(data,tmpl)}}
tmpl.cache={}
tmpl.encReg=/[<>&"'\x00]/g
tmpl.encMap={'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;',"'":'&#39;'}
tmpl.encode=function(s){return(s==null?'':''+s).replace(tmpl.encReg,function(c){return tmpl.encMap[c]||''})}
if(typeof define==='function'&&define.amd){define(function(){return tmpl})}else if(typeof module==='object'&&module.exports){module.exports=tmpl}else{$.tmpl=tmpl}})(this)