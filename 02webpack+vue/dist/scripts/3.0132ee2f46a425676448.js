webpackJsonp([3],{"+Poa":function(t,e,n){"use strict";var a=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"index-page"},[n("com-header"),t._v(" "),n("div",{staticClass:"main"},[n("router-view"),t._v(" "),n("com-instance-nav",{directives:[{name:"show",rawName:"v-show",value:t.leftInstanceNavShow,expression:"leftInstanceNavShow"}]})],1)],1)},r=[],i={render:a,staticRenderFns:r};e.a=i},"0xDb":function(t,e,n){"use strict";e.splitKDA=function(t){if(t.match(/(\d+)-(\d+)-(\d+)/))return t.split("-");throw new Error("kda should be like “K-D-A”")},e.execKDA=function(t,e,n){return 0===parseInt(e)?parseInt(t)+parseInt(n):(parseInt(t)+parseInt(n))/parseInt(e)},e.doubleToPer=function(t){return t?(100*t).toFixed(1)+"%":"0"},e.getScript=function(t,e,n){n=n||function(){};var a=document.createElement("script");a.type="text/javascript",a.src=t,a.setAttribute("charset","UTF-8"),a.setAttribute("data-page",e),a.setAttribute("async","true"),a.onload=function(){document.head.removeChild(a),n(null)},a.onerror=function(t){n(t)},document.head.appendChild(a)},e.removeScript=function(t){var e=document.querySelector('[data-page="'+t+'"]');null!==e&&document.head.removeChild(e)},e.throttle=function(t,e,n){var a=null,r=new Date;return function(i,o){var c=this,s=arguments,u=new Date;clearTimeout(a),u-r>=n?(t.apply(c,s),r=u):a=setTimeout(function(){t.apply(c,s)},e)}},e.hasOwnProperty=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.obj2Array=function(t){var n=[];for(var a in t)if(e.hasOwnProperty(t,a)){var r=t[a];n.push(r)}return n},e.stringify=function(t){var n="";for(var a in t)e.hasOwnProperty(t,a)&&null!==t[a]&&void 0!==t[a]&&(n+=a+"="+t[a]+"&");return n.slice(0,-1)},e.getUrlParams=function(){return this.$route.query},e.getQueryString=function(t){var e=new RegExp("(^|&)"+t+"=([^&]*)(&|$)","i"),n=window.location.search.slice(1).match(e);return null!=n?decodeURI(n[2]):null},e.isFunction=function(t){return"[object Function]"===Object.prototype.toString.call(t)},e.timeFormat=function(t,e){var n={"M+":t.getMonth()+1,"d+":t.getDate(),"h+":t.getHours(),"m+":t.getMinutes(),"s+":t.getSeconds(),"q+":Math.floor((t.getMonth()+3)/3),S:t.getMilliseconds(),"D+":t.getDay()};/(y+)/.test(e)&&(e=e.replace(RegExp.$1,(t.getFullYear()+"").substr(4-RegExp.$1.length)));for(var a in n)new RegExp("("+a+")").test(e)&&(e=e.replace(RegExp.$1,1===RegExp.$1.length?n[a]:("00"+n[a]).substr((""+n[a]).length)));return e},e.format=function(t,e){for(var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"0",a=0;a<e;a++)t=n+t;return t.substr(-e)}},"1tU2":function(t,e,n){"use strict";function a(t){n("52/4")}Object.defineProperty(e,"__esModule",{value:!0});var r=n("ddUD"),i=n.n(r);for(var o in r)"default"!==o&&function(t){n.d(e,t,function(){return r[t]})}(o);var c=n("3Vfo"),s=n("VU/8"),u=a,l=s(i.a,c.a,!1,u,"data-v-772ba0c2",null);e.default=l.exports},"2NXm":function(t,e,n){"use strict";function a(t){n("A8V2")}Object.defineProperty(e,"__esModule",{value:!0});var r=n("Ued4"),i=n.n(r);for(var o in r)"default"!==o&&function(t){n.d(e,t,function(){return r[t]})}(o);var c=n("+Poa"),s=n("VU/8"),u=a,l=s(i.a,c.a,!1,u,"data-v-5bfb913c",null);e.default=l.exports},"3Vfo":function(t,e,n){"use strict";var a=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"com-instance-nav"},[n("div",{directives:[{name:"show",rawName:"v-show",value:t.showNav,expression:"showNav"}],staticClass:"nav"},[n("nav",{staticClass:"app-nav"},[n("router-link",{staticClass:"com-instance-nav-item",attrs:{to:"/home"}},[t._v("回首页\n            ")]),t._v(" "),n("router-link",{staticClass:"com-instance-nav-item",attrs:{to:{name:"seat-selection",params:{game:t.game,id:t.id,origin:t.origin}}}},[t._v("选座\n            ")]),t._v(" "),n("router-link",{staticClass:"com-instance-nav-item",attrs:{to:{name:"bp",params:{game:t.game,id:t.id,origin:t.origin}}}},[t._v("BP\n            ")]),t._v(" "),n("router-link",{staticClass:"com-instance-nav-item",attrs:{to:{name:"gold-chart",params:{game:t.game,id:t.id,origin:t.origin}}}},[t._v("经济曲线\n            ")]),t._v(" "),n("router-link",{staticClass:"com-instance-nav-item",attrs:{to:{name:"dragon",params:{game:t.game,id:t.id,origin:t.origin}}}},[t._v("大小龙\n            ")]),t._v(" "),n("router-link",{staticClass:"com-instance-nav-item",attrs:{to:{name:"process",params:{game:t.game,id:t.id,origin:t.origin}}}},[t._v("进程\n            ")])],1)]),t._v(" "),n("div",{staticClass:"btn-wrap",on:{click:function(e){t.showNav=!t.showNav}}},[n("el-button",{staticClass:"show-btn",attrs:{type:"primary",icon:t.showNav?"el-icon-d-arrow-left":"el-icon-d-arrow-right"}})],1)])},r=[],i={render:a,staticRenderFns:r};e.a=i},"52/4":function(t,e){},"6sb0":function(t,e){},A8V2:function(t,e){},Jcel:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var a=n("0xDb"),r=function(t){return t&&t.__esModule?t:{default:t}}(a);e.default={name:"header",data:function(){return{isLocal:"local"===r.default.getQueryString("r")}},methods:{changeOrigin:function(){this.isLocal?window.location.href=window.location.href.replace("r=root","r=local"):window.location.href=window.location.href.replace("r=local","r=root")}}}},Ued4:function(t,e,n){"use strict";function a(t){return t&&t.__esModule?t:{default:t}}Object.defineProperty(e,"__esModule",{value:!0});var r=n("t9aS"),i=a(r),o=n("1tU2"),c=a(o);e.default={name:"index",components:{comHeader:i.default,comInstanceNav:c.default},data:function(){return{}},computed:{leftInstanceNavShow:function(){var t=this.$route.path,e=-1===t.indexOf("/home"),n=-1===t.indexOf("/instance");return e&&n}}}},ddUD:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default={name:"com-instance-nav",data:function(){return{showNav:!1}},computed:{id:function(){return this.$route.params.id},game:function(){return this.$route.params.game},origin:function(){return this.$route.params.origin}},updated:function(){}}},iBAt:function(t,e,n){"use strict";var a=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"common-header"},[n("router-link",{attrs:{to:"/home"}},[t._v("\n        OCR管理后台\n    ")]),t._v(" "),n("el-switch",{staticClass:"origin-box",staticStyle:{display:"inline-block"},attrs:{"active-color":"#13ce66","inactive-color":"#ff4949","active-text":"本地","inactive-text":"远端"},on:{change:t.changeOrigin},model:{value:t.isLocal,callback:function(e){t.isLocal=e},expression:"isLocal"}})],1)},r=[],i={render:a,staticRenderFns:r};e.a=i},t9aS:function(t,e,n){"use strict";function a(t){n("6sb0")}Object.defineProperty(e,"__esModule",{value:!0});var r=n("Jcel"),i=n.n(r);for(var o in r)"default"!==o&&function(t){n.d(e,t,function(){return r[t]})}(o);var c=n("iBAt"),s=n("VU/8"),u=a,l=s(i.a,c.a,!1,u,"data-v-f043049e",null);e.default=l.exports}});