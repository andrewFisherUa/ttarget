(function(d, b){
    var id = 'i' + Math.random().toString(16).slice(2);
    if(b['title']) d.write('<div id="' + id + '_title"><div>Новости Ttarget</div></div>');
    d.write('<div id="' + id + '"></div>');
    var e = d.createElement('script');
    e.type="text/javascript";
    e.src="//tt.ttarget.ru/s/tt3.js";
    e.async=true;
    e.onload = e.readystatechange = function(){
        if (!e.readyState || e.readyState == "loaded" || e.readyState == "complete") {
            e.onload = e.readystatechange = null;
            TT.createBlock({id: id, block: b['id'], count: b['count']});
        }
    };
    d.getElementsByTagName("head")[0].appendChild(e);
})(document, %BLOCK_VAR%);