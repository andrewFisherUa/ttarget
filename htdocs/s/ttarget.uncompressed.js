window['TT'] = window['TT'] || function() {
    var
        createRequest = function () {
            if (typeof XMLHttpRequest === 'undefined') {
                XMLHttpRequest = function () {
                    try { return new ActiveXObject("Msxml2.XMLHTTP.6.0"); }
                    catch (e) {}
                    try { return new ActiveXObject("Msxml2.XMLHTTP.3.0"); }
                    catch (e) {}
                    try { return new ActiveXObject("Msxml2.XMLHTTP"); }
                    catch (e) {}
                    try { return new ActiveXObject("Microsoft.XMLHTTP"); }
                    catch (e) {}
                    throw new Error("This browser does not support XMLHttpRequest.");
                };
            }
            return new XMLHttpRequest();
        },
        loadJs = function (url) {
            var e = document.createElement('script');
            e.setAttribute("type", "text/javascript");
            e.setAttribute("src", url);
            e.setAttribute("async", "async");
            document.getElementsByTagName("head")[0].appendChild(e);
        },
        externalStats = function(url){
            var req = createRequest();
            req.open('GET', url, true);
            req.send(null);
        };

    return {
        loadJs: loadJs,
        externalStats: externalStats,
    }
}();
var ttarget_showed, ttarget_checked, ttarget_old_onscrol, ttarget_div;
ttarget_showed = ttarget_checked = false;
ttarget_old_onscrol = document.onscroll;
ttarget_div = document.getElementById('ttarget_div');
document.onscroll = function(){
    var elt, elementTop, elementHeight, links, visibleTop, tt, src, s;
    if(!ttarget_checked){
        var links = e.getElementsByTagName("div");
        for(s=0; s<links.length; s++){
            tt = parseInt(links[s].currentStyle ? links[s].currentStyle.height : window.getComputedStyle(links[s]).height);
            if(tt != 0 && tt <= links[s].getElementsByTagName('a')[0].offsetHeight){
                links[s].getElementsByTagName('small')[0].style.display = 'none';
            }
        }
        ttarget_checked = true;
    }
    if(!ttarget_showed){
        elt = ttarget_div;
        elementTop = 0;
        elementHeight = elt.offsetHeight;
        while(elt) {
            elementTop += elt["offsetTop"];
            elt = elt.offsetParent;
        }
        visibleTop = (document.body.scrollTop != 0) ? document.body.scrollTop : document.documentElement.scrollTop;
        if((elementTop+elementHeight >= visibleTop) && (elementTop <= visibleTop + window.innerHeight)){
            links = ttarget_div.getElementsByTagName("a");
            if(links.length){
                ttarget_showed = true;
                tt = document.createElement('script');
                tt.type = 'text/javascript';
                tt.async = true;
                src = 'http://tt.ttarget.ru/show?p='+Math.random();
                for(s=0; s<links.length; s++){
                    src += '&id='+links[s].getAttribute('data-id');
                }
                tt.src = src;
                s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(tt, s);
            }
        }
    }
    if(typeof ttarget_old_onscrol == "function") ttarget_old_onscrol();
};
e=document.getElementById('ttarget');
if(e&&screen.width&&screen.height&&screen.width&&screen.height&&navigator.userAgent){
    h=e.getAttribute("data-height");
    w=e.getAttribute("data-width");
    if(h&&w){
        e=document.getElementById('ttarget_div');
        r = e.getAttribute('data-rotation');
        if(r != null){
            r = '&r='+r;
        }else{
            r = ''
        }
        document.write('<script type="text/javascript" src="http://tt.ttarget.ru/get/?w='+w+'&h='+h+'&p='+Math.random()+r+'"></script>');
    }
}