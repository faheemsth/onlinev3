var _self=typeof window!=="undefined"?window:typeof WorkerGlobalScope!=="undefined"&&self instanceof WorkerGlobalScope?self:{};var Prism=function(_self){var lang=/\blang(?:uage)?-([\w-]+)\b/i;var uniqueId=0;var _={manual:_self.Prism&&_self.Prism.manual,disableWorkerMessageHandler:_self.Prism&&_self.Prism.disableWorkerMessageHandler,util:{encode:function encode(tokens){if(tokens instanceof Token){return new Token(tokens.type,encode(tokens.content),tokens.alias)}else if(Array.isArray(tokens)){return tokens.map(encode)}else{return tokens.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/\u00a0/g," ")}},type:function(o){return Object.prototype.toString.call(o).slice(8,-1)},objId:function(obj){if(!obj["__id"]){Object.defineProperty(obj,"__id",{value:++uniqueId})}return obj["__id"]},clone:function deepClone(o,visited){visited=visited||{};var clone,id;switch(_.util.type(o)){case"Object":id=_.util.objId(o);if(visited[id]){return visited[id]}clone={};visited[id]=clone;for(var key in o){if(o.hasOwnProperty(key)){clone[key]=deepClone(o[key],visited)}}return clone;case"Array":id=_.util.objId(o);if(visited[id]){return visited[id]}clone=[];visited[id]=clone;o.forEach(function(v,i){clone[i]=deepClone(v,visited)});return clone;default:return o}},getLanguage:function(element){while(element&&!lang.test(element.className)){element=element.parentElement}if(element){return(element.className.match(lang)||[,"none"])[1].toLowerCase()}return"none"},currentScript:function(){if(typeof document==="undefined"){return null}if("currentScript"in document&&1<2){return document.currentScript}try{throw new Error}catch(err){var src=(/at [^(\r\n]*\((.*):.+:.+\)$/i.exec(err.stack)||[])[1];if(src){var scripts=document.getElementsByTagName("script");for(var i in scripts){if(scripts[i].src==src){return scripts[i]}}}return null}},isActive:function(element,className,defaultActivation){var no="no-"+className;while(element){var classList=element.classList;if(classList.contains(className)){return true}if(classList.contains(no)){return false}element=element.parentElement}return!!defaultActivation}},languages:{extend:function(id,redef){var lang=_.util.clone(_.languages[id]);for(var key in redef){lang[key]=redef[key]}return lang},insertBefore:function(inside,before,insert,root){root=root||_.languages;var grammar=root[inside];var ret={};for(var token in grammar){if(grammar.hasOwnProperty(token)){if(token==before){for(var newToken in insert){if(insert.hasOwnProperty(newToken)){ret[newToken]=insert[newToken]}}}if(!insert.hasOwnProperty(token)){ret[token]=grammar[token]}}}var old=root[inside];root[inside]=ret;_.languages.DFS(_.languages,function(key,value){if(value===old&&key!=inside){this[key]=ret}});return ret},DFS:function DFS(o,callback,type,visited){visited=visited||{};var objId=_.util.objId;for(var i in o){if(o.hasOwnProperty(i)){callback.call(o,i,o[i],type||i);var property=o[i],propertyType=_.util.type(property);if(propertyType==="Object"&&!visited[objId(property)]){visited[objId(property)]=true;DFS(property,callback,null,visited)}else if(propertyType==="Array"&&!visited[objId(property)]){visited[objId(property)]=true;DFS(property,callback,i,visited)}}}}},plugins:{},highlightAll:function(async,callback){_.highlightAllUnder(document,async,callback)},highlightAllUnder:function(container,async,callback){var env={callback:callback,container:container,selector:'code[class*="language-"], [class*="language-"] code, code[class*="lang-"], [class*="lang-"] code'};_.hooks.run("before-highlightall",env);env.elements=Array.prototype.slice.apply(env.container.querySelectorAll(env.selector));_.hooks.run("before-all-elements-highlight",env);for(var i=0,element;element=env.elements[i++];){_.highlightElement(element,async===true,env.callback)}},highlightElement:function(element,async,callback){var language=_.util.getLanguage(element);var grammar=_.languages[language];element.className=element.className.replace(lang,"").replace(/\s+/g," ")+" language-"+language;var parent=element.parentElement;if(parent&&parent.nodeName.toLowerCase()==="pre"){parent.className=parent.className.replace(lang,"").replace(/\s+/g," ")+" language-"+language}var code=element.textContent;var env={element:element,language:language,grammar:grammar,code:code};function insertHighlightedCode(highlightedCode){env.highlightedCode=highlightedCode;_.hooks.run("before-insert",env);env.element.innerHTML=env.highlightedCode;_.hooks.run("after-highlight",env);_.hooks.run("complete",env);callback&&callback.call(env.element)}_.hooks.run("before-sanity-check",env);if(!env.code){_.hooks.run("complete",env);callback&&callback.call(env.element);return}_.hooks.run("before-highlight",env);if(!env.grammar){insertHighlightedCode(_.util.encode(env.code));return}if(async&&_self.Worker){var worker=new Worker(_.filename);worker.onmessage=function(evt){insertHighlightedCode(evt.data)};worker.postMessage(JSON.stringify({language:env.language,code:env.code,immediateClose:true}))}else{insertHighlightedCode(_.highlight(env.code,env.grammar,env.language))}},highlight:function(text,grammar,language){var env={code:text,grammar:grammar,language:language};_.hooks.run("before-tokenize",env);env.tokens=_.tokenize(env.code,env.grammar);_.hooks.run("after-tokenize",env);return Token.stringify(_.util.encode(env.tokens),env.language)},tokenize:function(text,grammar){var rest=grammar.rest;if(rest){for(var token in rest){grammar[token]=rest[token]}delete grammar.rest}var tokenList=new LinkedList;addAfter(tokenList,tokenList.head,text);matchGrammar(text,tokenList,grammar,tokenList.head,0);return toArray(tokenList)},hooks:{all:{},add:function(name,callback){var hooks=_.hooks.all;hooks[name]=hooks[name]||[];hooks[name].push(callback)},run:function(name,env){var callbacks=_.hooks.all[name];if(!callbacks||!callbacks.length){return}for(var i=0,callback;callback=callbacks[i++];){callback(env)}}},Token:Token};_self.Prism=_;function Token(type,content,alias,matchedStr){this.type=type;this.content=content;this.alias=alias;this.length=(matchedStr||"").length|0}Token.stringify=function stringify(o,language){if(typeof o=="string"){return o}if(Array.isArray(o)){var s="";o.forEach(function(e){s+=stringify(e,language)});return s}var env={type:o.type,content:stringify(o.content,language),tag:"span",classes:["token",o.type],attributes:{},language:language};var aliases=o.alias;if(aliases){if(Array.isArray(aliases)){Array.prototype.push.apply(env.classes,aliases)}else{env.classes.push(aliases)}}_.hooks.run("wrap",env);var attributes="";for(var name in env.attributes){attributes+=" "+name+'="'+(env.attributes[name]||"").replace(/"/g,"&quot;")+'"'}return"<"+env.tag+' class="'+env.classes.join(" ")+'"'+attributes+">"+env.content+"</"+env.tag+">"};function matchGrammar(text,tokenList,grammar,startNode,startPos,rematch){for(var token in grammar){if(!grammar.hasOwnProperty(token)||!grammar[token]){continue}var patterns=grammar[token];patterns=Array.isArray(patterns)?patterns:[patterns];for(var j=0;j<patterns.length;++j){if(rematch&&rematch.cause==token+","+j){return}var patternObj=patterns[j],inside=patternObj.inside,lookbehind=!!patternObj.lookbehind,greedy=!!patternObj.greedy,lookbehindLength=0,alias=patternObj.alias;if(greedy&&!patternObj.pattern.global){var flags=patternObj.pattern.toString().match(/[imsuy]*$/)[0];patternObj.pattern=RegExp(patternObj.pattern.source,flags+"g")}var pattern=patternObj.pattern||patternObj;for(var currentNode=startNode.next,pos=startPos;currentNode!==tokenList.tail;pos+=currentNode.value.length,currentNode=currentNode.next){if(rematch&&pos>=rematch.reach){break}var str=currentNode.value;if(tokenList.length>text.length){return}if(str instanceof Token){continue}var removeCount=1;if(greedy&&currentNode!=tokenList.tail.prev){pattern.lastIndex=pos;var match=pattern.exec(text);if(!match){break}var from=match.index+(lookbehind&&match[1]?match[1].length:0);var to=match.index+match[0].length;var p=pos;p+=currentNode.value.length;while(from>=p){currentNode=currentNode.next;p+=currentNode.value.length}p-=currentNode.value.length;pos=p;if(currentNode.value instanceof Token){continue}for(var k=currentNode;k!==tokenList.tail&&(p<to||typeof k.value==="string");k=k.next){removeCount++;p+=k.value.length}removeCount--;str=text.slice(pos,p);match.index-=pos}else{pattern.lastIndex=0;var match=pattern.exec(str)}if(!match){continue}if(lookbehind){lookbehindLength=match[1]?match[1].length:0}var from=match.index+lookbehindLength,matchStr=match[0].slice(lookbehindLength),to=from+matchStr.length,before=str.slice(0,from),after=str.slice(to);var reach=pos+str.length;if(rematch&&reach>rematch.reach){rematch.reach=reach}var removeFrom=currentNode.prev;if(before){removeFrom=addAfter(tokenList,removeFrom,before);pos+=before.length}removeRange(tokenList,removeFrom,removeCount);var wrapped=new Token(token,inside?_.tokenize(matchStr,inside):matchStr,alias,matchStr);currentNode=addAfter(tokenList,removeFrom,wrapped);if(after){addAfter(tokenList,currentNode,after)}if(removeCount>1){matchGrammar(text,tokenList,grammar,currentNode.prev,pos,{cause:token+","+j,reach:reach})}}}}}function LinkedList(){var head={value:null,prev:null,next:null};var tail={value:null,prev:head,next:null};head.next=tail;this.head=head;this.tail=tail;this.length=0}function addAfter(list,node,value){var next=node.next;var newNode={value:value,prev:node,next:next};node.next=newNode;next.prev=newNode;list.length++;return newNode}function removeRange(list,node,count){var next=node.next;for(var i=0;i<count&&next!==list.tail;i++){next=next.next}node.next=next;next.prev=node;list.length-=i}function toArray(list){var array=[];var node=list.head.next;while(node!==list.tail){array.push(node.value);node=node.next}return array}if(!_self.document){if(!_self.addEventListener){return _}if(!_.disableWorkerMessageHandler){_self.addEventListener("message",function(evt){var message=JSON.parse(evt.data),lang=message.language,code=message.code,immediateClose=message.immediateClose;_self.postMessage(_.highlight(code,_.languages[lang],lang));if(immediateClose){_self.close()}},false)}return _}var script=_.util.currentScript();if(script){_.filename=script.src;if(script.hasAttribute("data-manual")){_.manual=true}}function highlightAutomaticallyCallback(){if(!_.manual){_.highlightAll()}}if(!_.manual){var readyState=document.readyState;if(readyState==="loading"||readyState==="interactive"&&script&&script.defer){document.addEventListener("DOMContentLoaded",highlightAutomaticallyCallback)}else{if(window.requestAnimationFrame){window.requestAnimationFrame(highlightAutomaticallyCallback)}else{window.setTimeout(highlightAutomaticallyCallback,16)}}}return _}(_self);if(typeof module!=="undefined"&&module.exports){module.exports=Prism}if(typeof global!=="undefined"){global.Prism=Prism}Prism.languages.markup={comment:/<!--[\s\S]*?-->/,prolog:/<\?[\s\S]+?\?>/,doctype:{pattern:/<!DOCTYPE(?:[^>"'[\]]|"[^"]*"|'[^']*')+(?:\[(?:[^<"'\]]|"[^"]*"|'[^']*'|<(?!!--)|<!--(?:[^-]|-(?!->))*-->)*\]\s*)?>/i,greedy:true,inside:{"internal-subset":{pattern:/(\[)[\s\S]+(?=\]>$)/,lookbehind:true,greedy:true,inside:null},string:{pattern:/"[^"]*"|'[^']*'/,greedy:true},punctuation:/^<!|>$|[[\]]/,"doctype-tag":/^DOCTYPE/,name:/[^\s<>'"]+/}},cdata:/<!\[CDATA\[[\s\S]*?]]>/i,tag:{pattern:/<\/?(?!\d)[^\s>\/=$<%]+(?:\s(?:\s*[^\s>\/=]+(?:\s*=\s*(?:"[^"]*"|'[^']*'|[^\s'">=]+(?=[\s>]))|(?=[\s/>])))+)?\s*\/?>/,greedy:true,inside:{tag:{pattern:/^<\/?[^\s>\/]+/,inside:{punctuation:/^<\/?/,namespace:/^[^\s>\/:]+:/}},"attr-value":{pattern:/=\s*(?:"[^"]*"|'[^']*'|[^\s'">=]+)/,inside:{punctuation:[{pattern:/^=/,alias:"attr-equals"},/"|'/]}},punctuation:/\/?>/,"attr-name":{pattern:/[^\s>\/]+/,inside:{namespace:/^[^\s>\/:]+:/}}}},entity:[{pattern:/&[\da-z]{1,8};/i,alias:"named-entity"},/&#x?[\da-f]{1,8};/i]};Prism.languages.markup["tag"].inside["attr-value"].inside["entity"]=Prism.languages.markup["entity"];Prism.languages.markup["doctype"].inside["internal-subset"].inside=Prism.languages.markup;Prism.hooks.add("wrap",function(env){if(env.type==="entity"){env.attributes["title"]=env.content.replace(/&amp;/,"&")}});Object.defineProperty(Prism.languages.markup.tag,"addInlined",{value:function addInlined(tagName,lang){var includedCdataInside={};includedCdataInside["language-"+lang]={pattern:/(^<!\[CDATA\[)[\s\S]+?(?=\]\]>$)/i,lookbehind:true,inside:Prism.languages[lang]};includedCdataInside["cdata"]=/^<!\[CDATA\[|\]\]>$/i;var inside={"included-cdata":{pattern:/<!\[CDATA\[[\s\S]*?\]\]>/i,inside:includedCdataInside}};inside["language-"+lang]={pattern:/[\s\S]+/,inside:Prism.languages[lang]};var def={};def[tagName]={pattern:RegExp(/(<__[\s\S]*?>)(?:<!\[CDATA\[(?:[^\]]|\](?!\]>))*\]\]>|(?!<!\[CDATA\[)[\s\S])*?(?=<\/__>)/.source.replace(/__/g,function(){return tagName}),"i"),lookbehind:true,greedy:true,inside:inside};Prism.languages.insertBefore("markup","cdata",def)}});Prism.languages.html=Prism.languages.markup;Prism.languages.mathml=Prism.languages.markup;Prism.languages.svg=Prism.languages.markup;Prism.languages.xml=Prism.languages.extend("markup",{});Prism.languages.ssml=Prism.languages.xml;Prism.languages.atom=Prism.languages.xml;Prism.languages.rss=Prism.languages.xml;(function(Prism){var string=/("|')(?:\\(?:\r\n|[\s\S])|(?!\1)[^\\\r\n])*\1/;Prism.languages.css={comment:/\/\*[\s\S]*?\*\//,atrule:{pattern:/@[\w-]+[\s\S]*?(?:;|(?=\s*\{))/,inside:{rule:/^@[\w-]+/,"selector-function-argument":{pattern:/(\bselector\s*\((?!\s*\))\s*)(?:[^()]|\((?:[^()]|\([^()]*\))*\))+?(?=\s*\))/,lookbehind:true,alias:"selector"},keyword:{pattern:/(^|[^\w-])(?:and|not|only|or)(?![\w-])/,lookbehind:true}}},url:{pattern:RegExp("\\burl\\((?:"+string.source+"|"+/(?:[^\\\r\n()"']|\\[\s\S])*/.source+")\\)","i"),greedy:true,inside:{"function":/^url/i,punctuation:/^\(|\)$/,string:{pattern:RegExp("^"+string.source+"$"),alias:"url"}}},selector:RegExp("[^{}\\s](?:[^{};\"']|"+string.source+")*?(?=\\s*\\{)"),string:{pattern:string,greedy:true},property:/[-_a-z\xA0-\uFFFF][-\w\xA0-\uFFFF]*(?=\s*:)/i,important:/!important\b/i,"function":/[-a-z0-9]+(?=\()/i,punctuation:/[(){};:,]/};Prism.languages.css["atrule"].inside.rest=Prism.languages.css;var markup=Prism.languages.markup;if(markup){markup.tag.addInlined("style","css");Prism.languages.insertBefore("inside","attr-value",{"style-attr":{pattern:/\s*style=("|')(?:\\[\s\S]|(?!\1)[^\\])*\1/i,inside:{"attr-name":{pattern:/^\s*style/i,inside:markup.tag.inside},punctuation:/^\s*=\s*['"]|['"]\s*$/,"attr-value":{pattern:/.+/i,inside:Prism.languages.css}},alias:"language-css"}},markup.tag)}})(Prism);Prism.languages.clike={comment:[{pattern:/(^|[^\\])\/\*[\s\S]*?(?:\*\/|$)/,lookbehind:true},{pattern:/(^|[^\\:])\/\/.*/,lookbehind:true,greedy:true}],string:{pattern:/(["'])(?:\\(?:\r\n|[\s\S])|(?!\1)[^\\\r\n])*\1/,greedy:true},"class-name":{pattern:/(\b(?:class|interface|extends|implements|trait|instanceof|new)\s+|\bcatch\s+\()[\w.\\]+/i,lookbehind:true,inside:{punctuation:/[.\\]/}},keyword:/\b(?:if|else|while|do|for|return|in|instanceof|function|new|try|throw|catch|finally|null|break|continue)\b/,"boolean":/\b(?:true|false)\b/,"function":/\w+(?=\()/,number:/\b0x[\da-f]+\b|(?:\b\d+\.?\d*|\B\.\d+)(?:e[+-]?\d+)?/i,operator:/[<>]=?|[!=]=?=?|--?|\+\+?|&&?|\|\|?|[?*/~^%]/,punctuation:/[{}[\];(),.:]/};Prism.languages.javascript=Prism.languages.extend("clike",{"class-name":[Prism.languages.clike["class-name"],{pattern:/(^|[^$\w\xA0-\uFFFF])[_$A-Z\xA0-\uFFFF][$\w\xA0-\uFFFF]*(?=\.(?:prototype|constructor))/,lookbehind:true}],keyword:[{pattern:/((?:^|})\s*)(?:catch|finally)\b/,lookbehind:true},{pattern:/(^|[^.]|\.\.\.\s*)\b(?:as|async(?=\s*(?:function\b|\(|[$\w\xA0-\uFFFF]|$))|await|break|case|class|const|continue|debugger|default|delete|do|else|enum|export|extends|for|from|function|(?:get|set)(?=\s*[\[$\w\xA0-\uFFFF])|if|implements|import|in|instanceof|interface|let|new|null|of|package|private|protected|public|return|static|super|switch|this|throw|try|typeof|undefined|var|void|while|with|yield)\b/,lookbehind:true}],number:/\b(?:(?:0[xX](?:[\dA-Fa-f](?:_[\dA-Fa-f])?)+|0[bB](?:[01](?:_[01])?)+|0[oO](?:[0-7](?:_[0-7])?)+)n?|(?:\d(?:_\d)?)+n|NaN|Infinity)\b|(?:\b(?:\d(?:_\d)?)+\.?(?:\d(?:_\d)?)*|\B\.(?:\d(?:_\d)?)+)(?:[Ee][+-]?(?:\d(?:_\d)?)+)?/,"function":/#?[_$a-zA-Z\xA0-\uFFFF][$\w\xA0-\uFFFF]*(?=\s*(?:\.\s*(?:apply|bind|call)\s*)?\()/,operator:/--|\+\+|\*\*=?|=>|&&=?|\|\|=?|[!=]==|<<=?|>>>?=?|[-+*/%&|^!=<>]=?|\.{3}|\?\?=?|\?\.?|[~:]/});Prism.languages.javascript["class-name"][0].pattern=/(\b(?:class|interface|extends|implements|instanceof|new)\s+)[\w.\\]+/;Prism.languages.insertBefore("javascript","keyword",{regex:{pattern:/((?:^|[^$\w\xA0-\uFFFF."'\])\s]|\b(?:return|yield))\s*)\/(?:\[(?:[^\]\\\r\n]|\\.)*]|\\.|[^/\\\[\r\n])+\/[gimyus]{0,6}(?=(?:\s|\/\*(?:[^*]|\*(?!\/))*\*\/)*(?:$|[\r\n,.;:})\]]|\/\/))/,lookbehind:true,greedy:true},"function-variable":{pattern:/#?[_$a-zA-Z\xA0-\uFFFF][$\w\xA0-\uFFFF]*(?=\s*[=:]\s*(?:async\s*)?(?:\bfunction\b|(?:\((?:[^()]|\([^()]*\))*\)|[_$a-zA-Z\xA0-\uFFFF][$\w\xA0-\uFFFF]*)\s*=>))/,alias:"function"},parameter:[{pattern:/(function(?:\s+[_$A-Za-z\xA0-\uFFFF][$\w\xA0-\uFFFF]*)?\s*\(\s*)(?!\s)(?:[^()]|\([^()]*\))+?(?=\s*\))/,lookbehind:true,inside:Prism.languages.javascript},{pattern:/[_$a-z\xA0-\uFFFF][$\w\xA0-\uFFFF]*(?=\s*=>)/i,inside:Prism.languages.javascript},{pattern:/(\(\s*)(?!\s)(?:[^()]|\([^()]*\))+?(?=\s*\)\s*=>)/,lookbehind:true,inside:Prism.languages.javascript},{pattern:/((?:\b|\s|^)(?!(?:as|async|await|break|case|catch|class|const|continue|debugger|default|delete|do|else|enum|export|extends|finally|for|from|function|get|if|implements|import|in|instanceof|interface|let|new|null|of|package|private|protected|public|return|set|static|super|switch|this|throw|try|typeof|undefined|var|void|while|with|yield)(?![$\w\xA0-\uFFFF]))(?:[_$A-Za-z\xA0-\uFFFF][$\w\xA0-\uFFFF]*\s*)\(\s*|\]\s*\(\s*)(?!\s)(?:[^()]|\([^()]*\))+?(?=\s*\)\s*\{)/,lookbehind:true,inside:Prism.languages.javascript}],constant:/\b[A-Z](?:[A-Z_]|\dx?)*\b/});Prism.languages.insertBefore("javascript","string",{"template-string":{pattern:/`(?:\\[\s\S]|\${(?:[^{}]|{(?:[^{}]|{[^}]*})*})+}|(?!\${)[^\\`])*`/,greedy:true,inside:{"template-punctuation":{pattern:/^`|`$/,alias:"string"},interpolation:{pattern:/((?:^|[^\\])(?:\\{2})*)\${(?:[^{}]|{(?:[^{}]|{[^}]*})*})+}/,lookbehind:true,inside:{"interpolation-punctuation":{pattern:/^\${|}$/,alias:"punctuation"},rest:Prism.languages.javascript}},string:/[\s\S]+/}}});if(Prism.languages.markup){Prism.languages.markup.tag.addInlined("script","javascript")}Prism.languages.js=Prism.languages.javascript;(function(){if(typeof self==="undefined"||!self.Prism||!self.document){return}var Prism=window.Prism;var LOADING_MESSAGE="Loading…";var FAILURE_MESSAGE=function(status,message){return"✖ Error "+status+" while fetching file: "+message};var FAILURE_EMPTY_MESSAGE="✖ Error: File does not exist or is empty";var EXTENSIONS={js:"javascript",py:"python",rb:"ruby",ps1:"powershell",psm1:"powershell",sh:"bash",bat:"batch",h:"c",tex:"latex"};var STATUS_ATTR="data-src-status";var STATUS_LOADING="loading";var STATUS_LOADED="loaded";var STATUS_FAILED="failed";var SELECTOR="pre[data-src]:not(["+STATUS_ATTR+'="'+STATUS_LOADED+'"])'+":not(["+STATUS_ATTR+'="'+STATUS_LOADING+'"])';var lang=/\blang(?:uage)?-([\w-]+)\b/i;function setLanguageClass(element,language){var className=element.className;className=className.replace(lang," ")+" language-"+language;element.className=className.replace(/\s+/g," ").trim()}Prism.hooks.add("before-highlightall",function(env){env.selector+=", "+SELECTOR});Prism.hooks.add("before-sanity-check",function(env){var pre=env.element;if(pre.matches(SELECTOR)){env.code="";pre.setAttribute(STATUS_ATTR,STATUS_LOADING);var code=pre.appendChild(document.createElement("CODE"));code.textContent=LOADING_MESSAGE;var src=pre.getAttribute("data-src");var language=env.language;if(language==="none"){var extension=(/\.(\w+)$/.exec(src)||[,"none"])[1];language=EXTENSIONS[extension]||extension}setLanguageClass(code,language);setLanguageClass(pre,language);var autoloader=Prism.plugins.autoloader;if(autoloader){autoloader.loadLanguages(language)}var xhr=new XMLHttpRequest;xhr.open("GET",src,true);xhr.onreadystatechange=function(){if(xhr.readyState==4){if(xhr.status<400&&xhr.responseText){pre.setAttribute(STATUS_ATTR,STATUS_LOADED);code.textContent=xhr.responseText;Prism.highlightElement(code)}else{pre.setAttribute(STATUS_ATTR,STATUS_FAILED);if(xhr.status>=400){code.textContent=FAILURE_MESSAGE(xhr.status,xhr.statusText)}else{code.textContent=FAILURE_EMPTY_MESSAGE}}}};xhr.send(null)}});Prism.plugins.fileHighlight={highlight:function highlight(container){var elements=(container||document).querySelectorAll(SELECTOR);for(var i=0,element;element=elements[i++];){Prism.highlightElement(element)}}};var logged=false;Prism.fileHighlight=function(){if(!logged){console.warn("Prism.fileHighlight is deprecated. Use `Prism.plugins.fileHighlight.highlight` instead.");logged=true}Prism.plugins.fileHighlight.highlight.apply(this,arguments)}})();(function(){var assign=Object.assign||function(obj1,obj2){for(var name in obj2){if(obj2.hasOwnProperty(name))obj1[name]=obj2[name]}return obj1};function NormalizeWhitespace(defaults){this.defaults=assign({},defaults)}function toCamelCase(value){return value.replace(/-(\w)/g,function(match,firstChar){return firstChar.toUpperCase()})}function tabLen(str){var res=0;for(var i=0;i<str.length;++i){if(str.charCodeAt(i)=="\t".charCodeAt(0))res+=3}return str.length+res}NormalizeWhitespace.prototype={setDefaults:function(defaults){this.defaults=assign(this.defaults,defaults)},normalize:function(input,settings){settings=assign(this.defaults,settings);for(var name in settings){var methodName=toCamelCase(name);if(name!=="normalize"&&methodName!=="setDefaults"&&settings[name]&&this[methodName]){input=this[methodName].call(this,input,settings[name])}}return input},leftTrim:function(input){return input.replace(/^\s+/,"")},rightTrim:function(input){return input.replace(/\s+$/,"")},tabsToSpaces:function(input,spaces){spaces=spaces|0||4;return input.replace(/\t/g,new Array(++spaces).join(" "))},spacesToTabs:function(input,spaces){spaces=spaces|0||4;return input.replace(RegExp(" {"+spaces+"}","g"),"\t")},removeTrailing:function(input){return input.replace(/\s*?$/gm,"")},removeInitialLineFeed:function(input){return input.replace(/^(?:\r?\n|\r)/,"")},removeIndent:function(input){var indents=input.match(/^[^\S\n\r]*(?=\S)/gm);if(!indents||!indents[0].length)return input;indents.sort(function(a,b){return a.length-b.length});if(!indents[0].length)return input;return input.replace(RegExp("^"+indents[0],"gm"),"")},indent:function(input,tabs){return input.replace(/^[^\S\n\r]*(?=\S)/gm,new Array(++tabs).join("\t")+"$&")},breakLines:function(input,characters){characters=characters===true?80:characters|0||80;var lines=input.split("\n");for(var i=0;i<lines.length;++i){if(tabLen(lines[i])<=characters)continue;var line=lines[i].split(/(\s+)/g),len=0;for(var j=0;j<line.length;++j){var tl=tabLen(line[j]);len+=tl;if(len>characters){line[j]="\n"+line[j];len=tl}}lines[i]=line.join("")}return lines.join("\n")}};if(typeof module!=="undefined"&&module.exports){module.exports=NormalizeWhitespace}if(typeof Prism==="undefined"){return}Prism.plugins.NormalizeWhitespace=new NormalizeWhitespace({"remove-trailing":true,"remove-indent":true,"left-trim":true,"right-trim":true});Prism.hooks.add("before-sanity-check",function(env){var Normalizer=Prism.plugins.NormalizeWhitespace;if(env.settings&&env.settings["whitespace-normalization"]===false){return}if(!Prism.util.isActive(env.element,"whitespace-normalization",true)){return}if((!env.element||!env.element.parentNode)&&env.code){env.code=Normalizer.normalize(env.code,env.settings);return}var pre=env.element.parentNode;if(!env.code||!pre||pre.nodeName.toLowerCase()!=="pre"){return}var children=pre.childNodes,before="",after="",codeFound=false;for(var i=0;i<children.length;++i){var node=children[i];if(node==env.element){codeFound=true}else if(node.nodeName==="#text"){if(codeFound){after+=node.nodeValue}else{before+=node.nodeValue}pre.removeChild(node);--i}}if(!env.element.children.length||!Prism.plugins.KeepMarkup){env.code=before+env.code+after;env.code=Normalizer.normalize(env.code,env.settings)}else{var html=before+env.element.innerHTML+after;env.element.innerHTML=Normalizer.normalize(html,env.settings);env.code=env.element.textContent}})})();Prism.plugins.NormalizeWhitespace.setDefaults({"remove-trailing":true,"remove-indent":true,"left-trim":true,"right-trim":true});