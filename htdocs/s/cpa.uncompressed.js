/** @const */
var debug = false;
window['ttargetCPA'] = {
    domReady: false,
    trackId: null,
    init: function(){
        ttargetCPA.trackId = ttargetCPA.getURLParameter('track');
        if(ttargetCPA.trackId){
            ttargetCPA.setCookie('trackId', ttargetCPA.trackId, 30*24*60*60);
        }else{
            ttargetCPA.trackId = ttargetCPA.getCookie('trackId');
        }

        if(!ttargetCPA.trackId){
            debug && console.log('track not found');
            return;
        }

        ttargetCPA.loadTargets();
        ttargetCPA.contentLoaded(window, function(e){
            debug && console.log('window loaded');
            ttargetCPA.domReady = true;
            ttargetCPA.attachTargets();
        });
    },

    contentLoaded: function(win, fn) {
        var done = false, top = true,
            doc = win.document, root = doc.documentElement,
            init = function(e) {
                if (e.type == 'readystatechange' && doc.readyState != 'complete') return;
                ttargetCPA.removeEvent((e.type == 'load' ? win : doc), e.type, init);
                if (!done && (done = true)) fn.call(win, e.type || e);
            },
            poll = function() {
                try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
                init('poll');
            };
        if (doc.readyState == 'complete') fn.call(win, 'lazy');
        else {
            if (doc.createEventObject && root.doScroll) {
                try { top = !win.frameElement; } catch(e) { }
                if (top) poll();
            }
            ttargetCPA.addEvent(doc, 'DOMContentLoaded', init);
            ttargetCPA.addEvent(doc, 'readystatechange', init);
            ttargetCPA.addEvent(win, 'load', init);
        }
    },

    addEvent: function (elem, type, handler){
        if (elem.addEventListener){
            elem.addEventListener(type, handler, false)
        } else {
            elem.attachEvent("on"+type, handler)
        }
    },

    removeEvent: function (elem, type, handler){
        if (elem.removeEventListener){
            elem.removeEventListener(type, handler, false)
        } else {
            elem.detachEvent("on"+type, handler)
        }
    },

    getElementsByClassNameCB: function(classname){
        if (!document.getElementsByClassName) {
            var elArray = [];
            var tmp = document.getElementsByTagName("*");
            var regex = new RegExp("(^|\\s)" + classname + "(\\s|$)");
            for ( var i = 0; i > tmp.length; i++ ) {
                if ( regex.test(tmp[i].className) ) {
                    elArray.push(tmp[i]);
                }
            }
            return elArray;
        }else{
            return document.getElementsByClassName(classname);
        }
    },

    processClick: function(e, targetId){
        ttargetCPA.syncRequest('http://tt.ttarget.ru/cpa/click.js?id='+targetId+'&track='+ttargetCPA.trackId+'&p='+Math.random());
    },

    syncRequest: function(url){
        var xmlhttp;
        if (window.XMLHttpRequest){
            xmlhttp=new XMLHttpRequest();
        }else{
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.open("GET", url, false);
        xmlhttp.send();
        return xmlhttp.responseText;
    },

    loadTargets: function(){
        var tt = document.createElement('script');
        tt.setAttribute('type', 'text/javascript');
        tt.setAttribute('src', 'http://tt.ttarget.ru/cpa.js?track='+ttargetCPA.trackId);
        tt.onreadystatechange = tt.onload = function(){
            if (!tt.readyState || /loaded|complete/.test(tt.readyState)) {
                ttargetCPA.attachTargets();
            }
        };
        document.getElementsByTagName("head")[0].appendChild(tt);
    },

    attachTargets: function(){
        debug && console.log('try to attach');
        if(typeof ttargetCPA['targets'] != "undefined" && ttargetCPA.domReady != false){
            for(var targetId in ttargetCPA['targets']){
                if(ttargetCPA['targets'].hasOwnProperty(targetId)){
                    var elements = ttargetCPA.getElementsByClassNameCB(ttargetCPA['targets'][targetId]);
                    debug && console.log('attaching elements: ', elements)
                    for (var i = 0; i < elements.length; i++){
                        if(!elements[i].hasAttribute('ttargetCPAAttached')){
                            ttargetCPA.addEvent(elements[i], 'click', function(targetId){ return function(e){ ttargetCPA.processClick(e,targetId); }; }(targetId));
                            elements[i].setAttribute('ttargetCPAAttached', true);
                        }
                    }
                }
            }
        }
    },

    getCookie: function(name){
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : null;
    },

    setCookie: function(name, value, props){
        props = props || {}
        var exp = props.expires
        if (typeof exp == "number" && exp) {
            var d = new Date()
            d.setTime(d.getTime() + exp*1000)
            exp = props.expires = d
        }
        if(exp && exp.toUTCString) { props.expires = exp.toUTCString() }

        value = encodeURIComponent(value)
        var updatedCookie = name + "=" + value
        for(var propName in props){
            updatedCookie += "; " + propName
            var propValue = props[propName]
            if(propValue !== true){ updatedCookie += "=" + propValue }
        }
        document.cookie = updatedCookie
    },

    getURLParameter: function(name){
        return decodeURIComponent(
            (new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20')
        )||null;
    }
};

ttargetCPA.init();
