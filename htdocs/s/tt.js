window.TT=window.TT||function(){var c=function(){"undefined"===typeof XMLHttpRequest&&(XMLHttpRequest=function(){try{return new ActiveXObject("Msxml2.XMLHTTP.6.0")}catch(a){}try{return new ActiveXObject("Msxml2.XMLHTTP.3.0")}catch(a){}try{return new ActiveXObject("Msxml2.XMLHTTP")}catch(a){}try{return new ActiveXObject("Microsoft.XMLHTTP")}catch(a){}throw Error("This browser does not support XMLHttpRequest.");});return new XMLHttpRequest};return{loadJs:function(a){var b=document.createElement("script");
        b.setAttribute("type","text/javascript");b.setAttribute("src",a);b.setAttribute("async","async");document.getElementsByTagName("head")[0].appendChild(b)},externalStats:function(a){var b=c();b.open("GET",a,!0);b.send(null)}}}();var ttarget_showed,ttarget_checked,ttarget_old_onscrol,e,tt,s;ttarget_showed=ttarget_checked=!1;ttarget_old_onscrol=document.onscroll;
document.onscroll=function(){var c,a,b,d;if(!ttarget_checked){a=e.getElementsByTagName("div");for(d=0;d<a.length;d++)b=parseInt(a[d].currentStyle?a[d].currentStyle.height:window.getComputedStyle(a[d]).height),0!=b&&b<=a[d].getElementsByTagName("a")[0].offsetHeight&&(a[d].getElementsByTagName("small")[0].style.display="none");ttarget_checked=!0}if(!ttarget_showed){c=e;a=0;for(b=c.offsetHeight;c;)a+=c.offsetTop,c=c.offsetParent;c=0!=document.body.scrollTop?document.body.scrollTop:document.documentElement.scrollTop;
    if(a+b>=c&&a<=c+window.innerHeight&&(a=e.getElementsByTagName("a"),a.length)){ttarget_showed=!0;b=document.createElement("script");b.type="text/javascript";b.async=!0;c="http://tt.ttarget.ru/show?p="+Math.random();for(d=0;d<a.length;d++)c+="&id="+a[d].getAttribute("data-id");b.src=c;d=document.getElementsByTagName("script")[0];d.parentNode.insertBefore(b,d)}}"function"==typeof ttarget_old_onscrol&&ttarget_old_onscrol()};
(e=document.getElementById("ttarget_div"))&&screen.width&&screen.height&&screen.width&&screen.height&&navigator.userAgent&&(h=e.getAttribute("data-height"),w=e.getAttribute("data-width"),h&&w&&(tt=document.createElement("script"),tt.type="text/javascript",tt.async=!0,tt.src="http://tt.ttarget.ru/get.js?w="+w+"&h="+h+"&p="+Math.random(),r=e.getAttribute("data-rotation"),null!=r&&(tt.src+="&r="+r),s=document.getElementsByTagName("script")[0],s.parentNode.insertBefore(tt,s)));