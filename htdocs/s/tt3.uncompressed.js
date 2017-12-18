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
            createBlock : function(params){
                params['domain'] = 'next1.serlive.ru';
                var jsPath = '/get.js',
                    cssPath = '/s/css',
                    showPath = '/show',
                    block,
                    blockShowed = false,
                    descriptionChecked = false,
                    oldOnScroll,
                    cssCache = {},
                    checkTeaserDescription = function () {
                        var links, s, tt;
                        links = getBlock().getElementsByTagName("div");
                        for (s = 0; s < links.length; s++) {
                            tt = parseInt(links[s].currentStyle ? links[s].currentStyle.height : window.getComputedStyle(links[s]).height, 10);
                            if (tt != 0 && tt <= links[s].getElementsByTagName('a')[0].offsetHeight) {
                                links[s].getElementsByTagName('small')[0].style.display = 'none';
                            }
                        }
                    },
                    getBlock = function () {
                        if (typeof block == "undefined") {
                            block = document.getElementById(params['id']);
                        }
                        return block;
                    },
                    isBlockVisible = function () {
                        var elt, elementTop, elementHeight, visibleTop;
                        elt = getBlock();
                        elementTop = 0;
                        elementHeight = elt.offsetHeight;
                        while (elt) {
                            elementTop += elt["offsetTop"];
                            elt = elt.offsetParent;
                        }
                        visibleTop = (document.body.scrollTop != 0) ? document.body.scrollTop : document.documentElement.scrollTop;
                        if ((elementTop + elementHeight >= visibleTop) && (elementTop <= visibleTop + window.innerHeight)) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    onScroll = function () {
                        if (!descriptionChecked) {
                            checkTeaserDescription();
                            descriptionChecked = true;
                        }
                        if (!blockShowed) {
                            if (isBlockVisible()) {
                                var links = getBlock().getElementsByTagName("a");
                                if (links.length) {
                                    blockShowed = true;
                                    var url = '//' + params['domain'] + showPath + '?p=' + Math.random();
                                    for (var s = 0; s < links.length; s++) {
                                        url += '&id=' + links[s].getAttribute('data-id');
                                    }
                                    blockShowed = true;
                                    loadJs(url);
                                }
                            }
                        }
                        if (typeof oldOnScroll == "function") oldOnScroll();
                    },
                    loadCss = function (url) {
                        if(typeof cssCache[url] != "undefined") {
                            applyCss(cssCache[url]);
                        }else{
                            var req = createRequest();
                            req.open('GET', url, true);
                            req.onreadystatechange = function () {
                                if (req.readyState == 4) {
                                    if (req.status == 200) {
                                        cssCache[url] = req.responseText;
                                        applyCss(cssCache[url]);
                                    }
                                }
                            };
                            req.send(null);
                        }
                    },
                    applyCss = function (text) {
                        text = text.replace(/%BLOCK_ID%/g, params['id']);
                        var e = document.createElement('style');
                        e.setAttribute("type", "text/css");
                        if (e.styleSheet) {
                            try {
                                e.styleSheet.cssText = text
                            } catch (err) {
                            }
                        } else {
                            e.appendChild(document.createTextNode(text));
                        }
                        document.getElementsByTagName("head")[0].appendChild(e)
                    };

                function init() {
                    oldOnScroll = document.onscroll;
                    document.onscroll = onScroll;
                    if (typeof params != "undefined") {
                        loadJs('//' + params['domain'] + jsPath
                            + '?w=240&h=' + (100 * params['count'])
                            + '&id=' + params['id']
                        );
                        loadCss('//' + params['domain'] + cssPath
                            + '/' + params['block'] + '.css'
                        );
                    }
                }

                init();
            }
        }
    }();
window['TT']['createBlock']=TT.createBlock;
window['TT']['loadJs']=TT.loadJs;
window['TT']['externalStats']=TT.externalStats;