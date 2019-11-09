<script>console.log("CONTAINER %cP85GDXB%c BUILD 2019-11-07 10:33:34","color:#00661d; text-decoration:underline","color:#000");

var sec = Math.floor((new Date()).getTime() / 1000) - 1573122814;console.log(Math.floor(sec / 3600)+"h "+Math.floor((sec - Math.floor(sec / 3600)*3600) / 60)+"m");(function(w,d,p){

    console.log('INIT CONTAINER');

        
  
 w.mt_ib = function(t, parent,block,type){

    w.mt_log(t, 'Parent', parent); 

    if(!type){

        w.mt_log(t, 'Append inside', block); 
        parent.appendChild(block);
                                   

    } else {

        if(type == -1){
            w.mt_log(t, 'Append before', block);
            parent.parentNode.insertBefore(block, parent);                                        
        }                                    

        if(type == 1){
            w.mt_log(t, 'Append after', block);
            parent.parentNode.insertBefore(block, parent.nextSibling);
        }

    }

}

w.mt_f = function(id,i,doc,t,simple){

    var el = false;

    if(id[0] == '.'){
        el = doc.getElementsByClassName(id.substr(1))[i];
    } else {
        el = doc.getElementById(id);
    }

    if(simple){
        if(!el){
            return false;
        } else {
            return true;
        }
    }

    if(!el){

        w.mt_log(t, 'Search in iframes');        

        var iFrames = doc.getElementsByTagName('iframe');
        //console.log(iFrames);
        for(var c = 0; c < iFrames.length; c++){

            if(!el){

                //console.log(iFrames[c]);

                try{

                    var iframedoc = iFrames[c].contentDocument || iFrames[c].contentWindow.document;
                    if(id[0] == '.'){
                        el = iframedoc.getElementsByClassName(id.substr(1))[c];
                    } else {
                        el = iframedoc.getElementById(id);
                    }

                } catch (e) {
                    //console.log('>>>>>>>>>>>>>>>>>>>> access exception');
                }

                if(el){
                    w.mt_log(t, 'Block found in iframe:',iFrames[c]);
                }

            }

        }

    }

    if(el){
        w.mt_log(t, 'Element',el);
    }

    return el;

}


w.mt_i = function(t_id,f_id,sw,sh,c){

    w.mt_log(t,'START ADD IFRAME '+t_id);

    var iFrameScript = d.createElement('iframe');
    
    iFrameScript.setAttribute('marginwidth','0');
    iFrameScript.setAttribute('marginheight','0');
    iFrameScript.setAttribute('frameborder','no');
    iFrameScript.border = 0;
    iFrameScript.scrolling = 'no';
    iFrameScript.margin = 0;
    iFrameScript.allowtransparency = true;
    iFrameScript.setAttribute('id',f_id);

    iFrameScript.width = (sw == 0?'100%':sw);
    iFrameScript.height = (sh == 0?'100%':sh);

    for(var i = 0;i< w.mt_t.length;i++){

        var t = w.mt_t[i];

        w.mt_log(t,'Check tag ID '+t.id);

        if(t.id == t_id && t.d != null){
            w.mt_log(t,'mt_add_iframe tag found with ID '+t.id);
            w.mt_log(t,'TAG',t);
            w.mt_log(t,'DIV',t.d);
            w.mt_log(t,'IFRAME',iFrameScript);
            t.d.appendChild(iFrameScript);            
            var iframeScriptDoc = iFrameScript.contentWindow.document;
            iframeScriptDoc.write('<html><head></head><body>'+c+'</body></html>');
            iframeScriptDoc.close();
        }

    }

    w.mt_log(t,'END ADD IFRAME');

}


w.mt_l = function(t,el){

    if(w.mt_special_param == 'pblooker_true_only'){
        w.mt_log(t,'abort passback looker');
        w.mt_l_result_found = false;
        return false;
    }

    w.mt_log(t,'Passback looker called for element ',el);
    
    el.childNodes.forEach(function(c){

        if(c.nodeType == 1){
            var tn = c.tagName;
            if(t.db) console.log('CHILD NAME ['+tn+']');
            if(!['HEAD','SCRIPT','LINK'].includes(tn.toUpperCase())){      

                if(tn == 'A'){
                    w.mt_log(t,'WHITE -> NOT1');
                    w.mt_l_result_found = true;
                    return false;
                }

                if(tn == 'IMG'){

                    w.mt_log(t,'image params [' + c.width + '/' + c.height + '] ['+c.naturalWidth+'/'+c.naturalHeight+'] url = '+c.getAttribute('src'));

                    if(c.width + c.height > 2 && c.naturalWidth + c.naturalHeight > 2){
                        w.mt_log(t,'WHITE -> NOT1');                        
                        w.mt_l_result_found = true;
                        return false;
                    }
                }

                if(tn == 'IFRAME'){

                    w.mt_log(t,'IS IFRAME '+c.src);

                    if(c.contentDocument == null){

                        w.mt_log(t,'WHITE -> NOT2');

                        var hidden = false;

                        if(c.style.display == 'none'){
                            hidden = true;
                        }

                        if(c.style.visibility == 'hidden'){
                            hidden = true;
                        }

                        if(!hidden){
                            w.mt_l_result_found = true;
                            return false;                                    
                        }

                    } else {
                        var ifd = c.contentDocument || c.contentWindow.document;                
                        return w.mt_l(t,ifd);
                    }

                    /*

                    if(c.contentDocument == null && c.contentWindow == null){
                        if(t.db) console.log('WHITE -> NOT2');
                        w.mt_l_result_found = true;
                        return false;                                    
                    } else {

                        if(c.contentDocument != null){
                            var ifd = c.contentDocument;
                        } else {

                            try{
                                var ifd = c.contentWindow.document;
                            } catch (ex){
                                if(t.db) console.log('WHITE -> NOT3');
                                console.log('crossdomain iframe');                                
                                w.mt_l_result_found = true;
                                return false; 
                            }

                        }                                
    
                        return w.mt_l(t,ifd);

                    }

                    */

                } else {
                    return w.mt_l(t,c);
                }
                
            }
        }

    }); 

    return true;    

}


w.mt_sc = function(t,div){
    if(div){

        w.mt_log(t, 'CLEAR BLOCK '+div.id, div);

        div.childNodes.forEach(function(child){

if(child.nodeType == 1){

console.log(child);
console.log('remove '+child.nodeType);
                console.log('remove '+child.getAttribute('mt-tag'));
                
if(child.getAttribute('mt-tag')){
child.innerHTML = '';
} else {
child.parentNode.removeChild(child);
}

}

        });
        
    }
}


w.mt_st = function(t,type,param){ //1 - simple statistick  2 - headerbidding

    w.mt_log(t, 'Statstick param to add (type = ' + type + ') ', param, 'statistick');

    var obj = {
        cust_id:t.id.split("_")[0],
        url_host:t.id.split("_")[1],   
        script_id: t.ls,
    };

    if(type == 1){

        obj['url_path'] = document.location.pathname;    
        obj['ad_displayed'] = (param?0:1);
        obj['passback'] = 0;
        w.mt_log(t, 'Look for passback script '+t.ls, param, 'statistick');

        for(var i = 0;i<t.p.length;i++){
            if(t.p[i].id==t.ls){
                obj['passback'] = 1;
            }
        }
        
    }

    if(type == 2){

        for(var key in param){
            obj[key] = param[key];
        }

    }

    if(t.gs){
        if(w.mt_4){
            obj['geo'] = w.mt_4;
        } else {
            obj['geo'] = '00';
        }
    } else {
        obj['geo'] = '00';
    }

    //w.mt_log(false, 'Statstick object to add:', obj, 'statistick');

    w.statistick_array = w.statistick_array || [];
    w.statistick_array[type] = w.statistick_array[type] || [];
    w.statistick_array[type].push(obj);

    //w.mt_log(false, 'Statstick array:', w.statistick_array[type], 'statistick');

    if(typeof w.mt_5 != 'undefined'){
        if(typeof w.mt_5[type] != 'undefined'){
            clearInterval(w.mt_5[type]);
        }
    } else {
        w.mt_5 = [];
        w.mt_5[type] = [];
    }

    var delay = 500;

    if(type == 1){
        if(w.mt_t.length == w.statistick_array[type].length){
            delay = 50;
        }    
    }

    //w.mt_log(false, 'Statstick before set timer:', null, 'statistick');

    w.mt_5[type] = setTimeout(function(){
        //w.mt_log(false, 'Statstick start with type: ('+type+') ', type, 'statistick');
        w.mt_ss(type);
    }, delay);

}

w.mt_ss = function(type){

    //w.mt_log(false, 'Statistick to send', w.statistick_array[type], 'statistick');

    var url = 'https://analisys.moneytag.tech/ifaddisplayedmulti';

    if(type == 2){
        url = 'https://analisyshb.moneytag.tech/';
    }

    //mt_log(false, 'Statistick url send', url, 'statistick');

    if(w.statistick_array[type] && w.statistick_array[type].length > 0){
        w.mt_xr(url, w.statistick_array[type]);
        w.statistick_array[type] = [];
    }

}


w.mt_xr = function(url,obj){
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-type','application/json; charset=utf-8');

    xhr.onload = function() {
        if (xhr.readyState === 4) {
            w.mt_log(false, 'Response:', xhr.responseText);
            if(xhr.status === 200){
               
            } else {
                console.error(xhr.statusText);
            }
        }
    }

    xhr.send(JSON.stringify(obj));
}

w.mt_send_error = function(t,code,code_sub){


    var exists = window.localStorage.getItem('mt_error_'+code);
    var time = window.localStorage.getItem('mt_error_'+code+'_time');
    var time_current = new Date().getTime();

    if(exists){
        if(time_current - time > 600){
            exists = false;
        }
    }

    if(!exists){

        localStorage.setItem('mt_error_'+code, code);
        localStorage.setItem('mt_error_'+code+'_time', new Date().getTime());

        var obj = {
            website_id:t.id.split("_")[1],
            tag_id:t.id.split("_")[0],
            script_id: t.ls,
            code: code,
            sub:code_sub
        };

        var rnd = Math.random();

        if(rnd < 0.2){
            w.mt_xr('https://errors.moneytag.tech',obj);
        }
        
    }

}


//headerbidding.js

w.mt_prebid_timeout_default = 2000;
w.mt_prebid_timeout = w.mt_prebid_timeout_default;
w.mt_prebid_timeout_changed = false;

w.mt_h = function(t,f){
    
    w.mt_log(t,'> headerbissing called', null, 'headerbidding,bold');

    if(t.headerbidding_delay < 200){
        t.headerbidding_delay = 200;
    }

    if(t.headerbidding_delay != w.mt_prebid_timeout){
        if(!w.mt_prebid_timeout_changed){
            w.mt_prebid_timeout_changed = true;
            w.mt_prebid_timeout = t.headerbidding_delay;
            w.mt_log(t,'> set prebid timeout to ' + w.mt_prebid_timeout, null, 'headerbidding');
        } else {
            if(w.mt_prebid_timeout < t.headerbidding_delay){
                w.mt_prebid_timeout = t.headerbidding_delay;
                w.mt_log(t,'> set prebid timeout to ' + w.mt_prebid_timeout, null, 'headerbidding');
            }
        }
    }

    w.mt_log(t,'> hb timeout', w.mt_prebid_timeout, 'headerbidding,bold');

    w.mt_1 = w.mt_1 || [];

    for(var di = 0; di < f.au.length; di++){
        w.mt_log(t,'> add to prebid tags ' + f.au[di].code, null, 'headerbidding');
        w.mt_1[f.au[di].code] = t;
        if(t.db) console.log(w.mt_1);                    
        w.mt_i(t.id,f.au[di].code + '_iframe', 1, 1, '');                                      
    }

    w.mt_log(t,'> prebid tags: ', w.mt_1, 'headerbidding');
    w.mt_log(t,'> PBJS AT INIT: ',window.pbjs, 'headerbidding');


    if(typeof w.mt_addunits__storage == 'undefined' || typeof w.mt_2 != 'undefined'){
        w.mt_addunits__storage = [];
        w.mt_log(t,'> CREATE STORAGE ',null, 'headerbidding');
        w.mt_log(false,'> CREATE STORAGE ',null, 'headerbidding');
    }

    w.mt_addunits__storage.push(f.au[0]);

    w.pbjs = w.pbjs || {};
    w.pbjs.que = w.pbjs.que || [];

    var ad = w.mt_addunits__storage;

    w.mt_log(t,'> STORAGE VALUE', ad , 'headerbidding');

    var x = function(){

        w.mt_2 = true;

        w.mt_log(false,'>>>>>>>>>> START PBJS QUE >>>>>>>>>>>', ad, 'headerbidding');

        w.pbjs.setConfig({
            debug: true,
            cache: {
                url: false
            },
            usersync: {
                userIds: [{
                    name: "id5Id",
                    params: {
                        partner: 219
                    },
                    storage: {
                        type: "cookie",
                        name: "pbjs-id5id",
                        expires: 5
                    }
                }],
                syncDelay: 1000
            }
        });

         w.pbjs.aliasBidder('rubicon','rubiconclassic');  w.pbjs.aliasBidder('rubicon','rubiconbrand');  w.pbjs.aliasBidder('appnexus','aliancegravity');  w.pbjs.aliasBidder('appnexus','mediasquareclass');  w.pbjs.aliasBidder('appnexus','mediasquaremax');  w.pbjs.aliasBidder('appnexus','apx2');  w.pbjs.aliasBidder('appnexus','apx3');  w.pbjs.aliasBidder('appnexus','apx4');  w.pbjs.aliasBidder('appnexus','apx5');  w.pbjs.aliasBidder('appnexus','mediasquarebrand'); 

        /*
        w.pbjs.aliasBidder("appnexus","aliancegravity");
        w.pbjs.aliasBidder("appnexus","mediasquarebrand");
        w.pbjs.aliasBidder("appnexus","mediasquareclass");
        w.pbjs.aliasBidder("appnexus","mediasquaremax");
        w.pbjs.aliasBidder("appnexus","apx2");
        w.pbjs.aliasBidder("appnexus","apx3");
        w.pbjs.aliasBidder("appnexus","apx4");
        w.pbjs.aliasBidder("appnexus","apx5");
        */
       
        w.pbjs.requestBids({
            adUnits: ad,
            timeout: w.mt_prebid_timeout,

            bidsBackHandler: function(bids_raw){

                w.mt_log(false,'> start handler', w.pbjs, 'headerbidding');

                var response = w.pbjs.getAdserverTargeting();
                var no_bids = w.pbjs.getNoBids();

                w.mt_log(false,'> response', response, 'headerbidding');
                w.mt_log(false,'> bids raw', bids_raw, 'headerbidding');                
                w.mt_log(false,'> no bids',  no_bids,  'headerbidding');
                w.mt_log(false,'> request',  ad,       'headerbidding');

                for(var iframeID in response)
                {

                    var tag = w.mt_1[iframeID];

                    w.mt_log(false, '> HB RESPONSE FOR DIV', iframeID, 'headerbidding');

                    var process = false;

                    for(var a in ad){
                        if(ad[a].code == iframeID){
                            process = true;
                        }
                    }

                    w.mt_log(false, '> process = ', process, 'headerbidding');                    

                    if(process)
                    {

                        var unit = response[iframeID];
                        var result_found = true;

                        w.mt_log(false, '> UNIT FOR '+iframeID, unit, 'headerbidding');
            
                        if (unit && unit['hb_adid']) {

                            var iframePrebid = document.getElementById(iframeID+'_iframe');
                            var iframePrebidDoc = iframePrebid.contentWindow.document;           
                            
                            w.mt_log(false, '> iFrame ', iframePrebid, 'headerbidding');
                            
                            //console.log(iframePrebidDoc);

                            if(unit['hb_native_title']){

                                w.mt_log(false, '> RENDER NATIVE', bids_raw[iframeID], 'headerbidding');
                                
                                var original = null;
                                
                                for(var bid in bids_raw[iframeID].bids){
                                    if(bids_raw[iframeID].bids[bid].adId == unit.hb_adid){
                                        original = bids_raw[iframeID].bids[bid];
                                    }
                                }
                                
                                w.mt_rn(unit, original, tag);

                            } else {

                                w.pbjs.renderAd(iframePrebidDoc, unit['hb_adid']);

                            }

                            w.mt_log(tag, '> CALL PASSBACK white = false', null, 'headerbidding');
                            //w.setTimeout(function(){
                                w.mt_p({
                                    t:tag,
                                    white:false,
                                    direct:true
                                });
                            //},1);

                        } else {
                            
                            result_found = false;

                            w.mt_log(tag, '> CALL PASSBACK white = true', null, 'headerbidding');
                            //w.setTimeout(function(){
                                w.mt_p({
                                    t:tag,
                                    white:true,
                                    direct:true
                                });
                            //},1);

                        }

                        //
                        //  //
                        //  //  //  Start statistick headerbidding 
                        //  //
                        //

                        var partners_involved = new Array();
                        var auction_id = '';
                        var media_format = '';
                        var currency = '';

                        if(result_found){

                            //w.mt_log(false, '> START STATISTICK', iframeID, 'headerbidding');

                            for(var bid_key in bids_raw[iframeID].bids){

                                var winner = false;
                                var bid = bids_raw[iframeID].bids[bid_key];

                                partners_involved.push(bid.bidderCode);
                                auction_id = bid.auctionId;          
                                media_format = bid.mediaType;         
                                currency = bid.currency;

                                if(bid.adId == unit.hb_adid){
                                    var winner = true;
                                }

                                var obj = {
                                    partner: bid.bidderCode,
                                    no_bid:false,
                                    fail_bid:false,
                                    is_winner: winner,                                
                                    cpm: bid.cpm,
                                    currency: bid.currency,
                                    auction: bid.auctionId,
                                    status:bid.statusMessage,
                                    time_response: (bid.timeToRespond/1000),
                                    media_type: bid.mediaType,                                    
                                }

                                w.mt_log(false, '> BID', bid, 'headerbidding');
                                w.mt_log(false, '> BID OBJ ', obj, 'headerbidding');

                                w.mt_st(tag, 2, obj);

                            }

                        }

                        if(no_bids.hasOwnProperty(iframeID)){
                            
                            for(var bid_key in no_bids[iframeID].bids){

                                var bid = no_bids[iframeID].bids[bid_key];

                                //w.mt_log(false, '> NO BID', bid, 'headerbidding');

                                partners_involved.push(bid.bidder);
                                auction_id = bid.auctionId;

                                if(media_format == ''){
                                    for(var key in bid.mediaTypes){
                                        media_format = key;
                                    }
                                }

                                var obj = {
                                    partner: bid.bidder,
                                    no_bid:true,
                                    fail_bid:false,
                                    auction: bid.auctionId,
                                    script_id: tag.ls,
                                    media_type: media_format,
                                    currency:currency,
                                    time_response: (bid.timeToRespond/1000),
                                    
                                }

                                w.mt_st(tag, 2, obj);

                            }

                        }

                        if(auction_id == '') auction_id = tag.id + '_' + tag.ls +'_'+ Date.now();

                        //w.mt_log(false, 'ad = ', ad, 'headerbidding');

                        for(var i = 0; i < ad.length;i++){

                            var bid = ad[i];

                            //w.mt_log(false, 'ad[i]', ad[i], 'headerbidding');

                            if(media_format == ''){
                                for(var key in bid.mediaTypes){
                                    media_format = key;
                                }
                            }

                            if(bid.code == iframeID){

                                //w.mt_log(false, 'code found', ad[i].code, 'headerbidding');
                                for(var b = 0; b < bid.bids.length; b++){

                                    //w.mt_log(false, 'check partner '+bid.bids[b].bidder, partners_involved, 'headerbidding');

                                    if(!partners_involved.includes(bid.bids[b].bidder)){

                                        //OBJ_CREATION
                                        var obj = {
                                            partner: bid.bids[b].bidder,
                                            no_bid: false,
                                            fail_bid: true,
                                            auction: auction_id,
                                            script_id: tag.ls,
                                            media_type: media_format,
                                            time_response: 0
                                        }                                        

                                        w.mt_st(tag, 2, obj);

                                    }
                                }
                            }

                        }
                        
                        //End statistick headerbidding

                    }
                    
                    w.pbjs.removeAdUnit(iframeID);                    

                }

                w.mt_prebid_timeout_changed = false;
                w.mt_prebid_timeout = w.mt_prebid_timeout_default;

            }
        });

    }

    w.mt_log(t, '> tag first run', t.f, 'headerbidding');
    w.mt_log(t, '> tag script loaded', w.mt_3, 'headerbidding');
    w.mt_log(t, '> tag script loaded end', w.mt_3_end, 'headerbidding');

    if(typeof w.mt_3_end == 'undefined'){
        w.mt_log(false, '> ADUNITS SET', null, 'headerbidding');        
        w.pbjs.que[0] = x;
    } else {
        w.mt_log(false, '> ADUNITS PUSH', null, 'headerbidding');
        x();
    }

    w.mt_log(false, '> END HB', null, 'headerbidding');

    if(typeof w.mt_3 == 'undefined'){

        w.mt_log(false, '> Start load PDJS library', null, 'headerbidding');

        w.mt_3 = true;
                
        var s = d.createElement('script');
        s.type = 'text/javascript';
        s.src = '//storage.googleapis.com/headerbidding/prebid-moneytag-big.js';    
        s.onload = function(){
            w.mt_log(false, '> PDJS library loaded', null, 'headerbidding');
            w.mt_3_end = true;
        }                    
        
        w.setTimeout(function(){
            var b = d.querySelector('body').firstChild;
            w.mt_log(false, '> append PDJS library to', b, 'headerbidding');    
            b.parentNode.insertBefore(s, b);        
        }, 100);
    
    }

}            

String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.split(search).join(replacement);
}; 

w.mt_rn = function(bid,original,tag){
    
    var h = 'hb_native_';
    var trim_length = 127;
    
    var tpl = '<div style=\"width: 300px; height: 250px; border: 1px solid black; cursor: pointer;\" onclick=\"document.location.href=\'[URL]\'\">';
    tpl += '<img style=\"margin: 5px 5px 0px 5px;\" align=\"left\" width=\"142px\" height=\"auto\" src=\"[IMG]\"/>';
    tpl += '<div style=\"margin: 5px; font-size: 16px;\">';
            tpl += '<div style=\"margin-bottom: 5px;\"><b>[TITLE]</b></div>';
            tpl += '<p style=\"text-overflow: ellipsis;\">[BODY]</p>';
            tpl += '<div style=\"margin-top: 2px;\"><b>[BRAND]</b></div>';
            tpl += '<div>';            
                tpl += '<img style=\"float: right;\" width=\"42px\" height=\"auto\" src=\"[ICON]\" style=\"[ICON_STYLE]\" />';            
            tpl += '</div>';
        tpl += '</div>';
    tpl += '</div>';
    
    if(tag.nt!=''){
        tpl = tag.nt;
    }
    
    for(var n in original.native.impressionTrackers){
        tpl+='<img src=\"'+original.native.impressionTrackers[n]+'\" style=\"width:0px; height:0px;\">';
    }
    
    if(bid[h+'body'].length > trim_length){
        bid[h+'body'] = bid[h+'body'].substring(0,127) + '...';
    }
    
    tpl = tpl.replaceAll('[URL]',bid[h+'linkurl']);
    tpl = tpl.replaceAll('[IMG]',bid[h+'image']);
    tpl = tpl.replaceAll('[TITLE]',bid[h+'title']);
    tpl = tpl.replaceAll('[BODY]',bid[h+'body']);
    tpl = tpl.replaceAll('[BRAND]',bid[h+'brand']);
    //tpl = tpl.replaceAll('[VECTA]',bid[h+'brand']);
    if(bid[h+'icon']){
        tpl = tpl.replaceAll('[ICON]',bid[h+'icon']);
    } else {
        tpl = tpl.replaceAll('[ICON_STYLE]','display:none;');
    }
                    
    w.mt_i(tag.id,'test_'+tag.id,0,0,tpl);

    //tag.d.innerHTML = tpl;
}



//devices.js

w.mt_m = function(){    

    this.n = navigator.userAgent;

    this.A = function() {
        return this.n.match(/Android/i);
    }
    this.BB = function() {
        return this.n.match(/BlackBerry/i);
    };
    this.i = function() {
        return this.n.match(/iPhone|iPad|iPod/i);
    };
    this.O = function() {
        return this.n.match(/Opera Mini/i);
    };
    this.W = function() {
        return this.n.match(/IEMobile/i);
    };
    this.any = function() {
        return (this.A() || this.BB() || this.i() || this.O() || this.W());
    };
    
};


//refresh.js

w.mt_v = function(t,el,w) {
        
    if(w.frameElement) {

        el = w.frameElement;
        var b = el.getBoundingClientRect();
        if(b.top + b.height / 2 > 0 && b.top + b.height / 2 < parent.window.innerHeight){
            return true;
        }
        return false;

    } else {

        if(el){

            var elt = el.offsetTop;
            var ell = el.offsetLeft;
            var elw = el.offsetWidth;
            var elh = el.offsetHeight;
        
            while(el.offsetParent) {

                el = el.offsetParent;
                elt += el.offsetTop;
                ell += el.offsetLeft;
                
            }
        
            return (
                elt + elh / 2 > w.pageYOffset && elt + elh / 2 < w.pageYOffset + w.innerHeight
            );

        } else {

            return false;

        }

    }

}

w.setTimeout(function(){

    if(typeof w.mt_0 == 'undefined'){

        w.mt_0 == 1;
        w.mt_time_old = p.now();
        

        w.setInterval(function(){

            w.mt_time_new = p.now();
            var delta_time = w.mt_time_new - w.mt_time_old;
            w.mt_time_old = p.now();                            

            if(d.visibilityState == 'visible'){

                w.mt_t.forEach(function(t){

                    if(t.a && t.rt > 0 && !t.rm && ((t.pa && t.s.length == 0) || t.s.length > 0)){

                        if(w.mt_v(t,t.d,window)){
                            t.rs += delta_time;                            
                            w.mt_log(t, 'time:', t.rs);             
                        }

                        if(t.rs > t.rt){

                            t.rs = 0;
                            //w.mt_clear_block(t);
                            var style = w.getComputedStyle(t.d, null);
                            var height = style.getPropertyValue('height');                                            
                            t.d.style.minHeight = height;

                            if(t.s.length > 0){
                                t.d.innerHTML = '';                                
                                w.mt_log(t, 'Block cleared:', t.d);                                
                            }

                            w.mt_s(t);

                        }

                    }

                });
            }
        },500);

    }

},3000);



w.mt_log_proc_style = function(s){    
    
    if(s){
    
        var consoleStyles = {
            'execute': 'background: #ffffc8',       
            'passback': 'background: #cddef7',
            'statistick': 'background: #FFF; color:#000',
            'headerbidding':'background: #e6d4fc',
            'system':'font-style:italic',
            'bold': 'font-weight: bold',
            'comment':'color: #DDD'
        };
        
        s = s.split(',');            
        
        for(var i = 0; i < s.length; i++){
            if(typeof consoleStyles[s[i]] != 'undefined'){
                s[i] = consoleStyles[s[i]];
            }
        }
        
        return s;
     
    } else {
        return new Array();
    }

}

w.mt_log = function(t,msg1,msg2,style1,style2){

    style1 = w.mt_log_proc_style(style1);
    style2 = w.mt_log_proc_style(style2);
    
    if(!t || t.db){

        if(!t){            
            var t = { id:'sys', do:'sys'};
            style1.push('font-style: italic');
        }
    
        var text = ''; 
        var d =  new Date();
        text+= '['+t.id+'/'+(t.do?t.do:'')+']['+('0' +d.getMinutes()).slice(-2)+':'+('0' +d.getSeconds()).slice(-2)+':'+('00' + d.getMilliseconds()).slice(-3)+'] %c%s ';

        if(msg2 != null && typeof msg2 != 'undefined'){
        
            if(typeof msg2 == 'object'){
                if(msg2 instanceof HTMLElement){
                    text+= '%o';
                } else {
                    text+= '%O';
                }
            } else {
                if(typeof msg2 == 'string' || typeof msg2 == 'boolean'){
                    text+= '%s';
                } else {
                    text+= '%d';
                }
            }
            
        }

        console.log(text, style1.join(';'), msg1, style2.join(';'), msg2);

    }
    
}

w.mt_log_group = function(t,name,start){

    if(!t){        
        var t = {debug:true, id:'sys', do:'sys'};        
    }    

    if(t.db){
        if(start){
            console.group('>> ['+t.id+'/'+(t.do?t.do:'')+'] '+name);
        } else {
            console.groupEnd();
        }
    }
}

// DEFAULT FUNCTIONS main.js


w.mt_d = function(s){

    if(s[0] && s[1] && s[2]) return true;
    
    var v = new w.mt_m();
    var width = w.frameElement?parent.window.innerWidth:w.innerWidth;

    if(s[2] && v.any() && width <= 780){     
        console.log('RETURN MOBILE');               
        return true;                    
    }

    if(s[1] && v.any() && width > 780){             
        console.log('RETURN TABLET');
        return true;                    
    }

    if(s[0] && !v.any()){
        console.log('RETURN DESCKTOP');
        return true;
    }

    return false;

}


//START GLOBAL VARS
w.mt_4 = false;
w.mt_l_result_found = false;
w.mt_special_param = '';
//END GLOBAL VARS

w.mt_a = function(){

    if(window.location.href.indexOf('#')>-1){
        w.mt_special_param = window.location.href.split('#')[1];
        w.mt_log(false,'> init with special param ', '['+w.mt_special_param+']');
    }

    w.mt_log(false,'> mt activate called');

    var start = false;

    if(typeof w.mt_5 == 'undefined'){
        start = true;
    } else {
        if(w.mt_6){
            w.mt_log(false,'> START LATER TAGS');   
            start = true;
            
        }
    }

    if(start){

        w.mt_log(false,'> set mt activate timer');

        w.mt_5 = setTimeout(function(){
            
            w.mt_log(false,'> timer called',w.mt_t);            

            var geo = false;
            var geo_statistick = false;

            w.mt_t.forEach(function(t) {
              
              console.log('tag id = '+t.id);
              console.log('tag geo = '+t.g);
              console.log('tag countries length = '+t.c.length);

                if(t.g && t.c.length>0){
                    geo = true;
                }
                if(t.gs){
                    geo_statistick = true;
                }

            });

            w.mt_log(false,'> geo = ', geo);
            w.mt_log(false,'> geo_statistick = ', geo_statistick);

            //
            if(geo){
                w.mt_get_geo(true);
            } else {
                if(geo_statistick){
                    w.mt_get_geo(false);
                }
                w.mt_pt();
            }

        }, 50);

    }

}

w.is_valid_json = function(){

}

w.mt_get_geo = function(process_tags){

    var time = new Date().getTime();

    if(localStorage.getItem("mt_gcode_country") == null || 
        (localStorage.getItem("mt_gcode_time") && time - localStorage.getItem("mt_gcode_time") > 3600*3)){
    
        w.mt_log(false,'> geolocation start');
    
        if(typeof w.mt_7 == 'undefined'){

            w.mt_7 = true;

            var xhr = new XMLHttpRequest();

            xhr.onload = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        w.mt_log(false,'> GEO result', xhr.responseText);
                        w.mt_4 = xhr.responseText.trim();
                        localStorage.setItem('mt_gcode_country', w.mt_4);
                        localStorage.setItem('mt_gcode_time', new Date().getTime());
                        if(process_tags){
                            w.mt_pt();
                        }
                    } else {
                        console.error(xhr.statusText);
                    }
                }
            }

            xhr.open('POST', 'https://gan.moneytag.tech', true);
            xhr.send(null);

        }

    } else {

        w.mt_4 = localStorage.getItem("mt_gcode_country");
        w.mt_log(false,'> GEO result from storage', w.mt_4); 
        if(process_tags){
            w.mt_pt();
        }

    }

}

w.mt_pt = function(){

    w.mt_log(false, '> process called');

    w.mt_t.forEach(function(t){

        w.mt_log_group(t,'PROCESS',true);
        w.mt_log(t,'PROCESS');

        var xw = w;
        var xd = d;           

        if(!t.hasOwnProperty('da') || (t.hasOwnProperty('reactivate') && t.reactivate)){

            //var width = w.frameElement?parent.window.innerWidth:w.innerWidth;
            if(w.frameElement){

                w.mt_log(t,'Block inside iframe');

                if(!w.mt_f(t.di,t.n,xd,t,true)){
                    w.mt_log(t,'Block is not found inside iFrame');
                    xw = parent.window;
                    xd = parent.window.document;
                } else {
                    w.mt_log(t,'Block succefuly found inside iFrame');
                }

            }

            var id = 'mt_'+t.id;

            if(t.di != ''){
                w.mt_log(t,'GET DIV PARENT WITH ID ', t.di);
                t.d_parent = w.mt_f(t.di, t.n, xd, t);
            }

            if(t.s.length == 0 && t.p.length == 0){

                w.mt_log(t, 'Activate as custom statistick tag '+id);

                if(t.di != ''){
                    if(!t.d_parent){
                        t.a = false;
                        w.mt_log(t, 'Disactivate Tag (div not found)');
                    } else {
                        t.da = true;
                    }
                } else {
                    w.mt_st(t, 1, false);
                }

            } else {

                if((!w.mt_f(id,0,xd,t) && t.s.length > 0) || t.s.length == 0){

                    w.mt_log(t, 'Div for tag is not found, must be created with id '+id);

                    var nd = d.createElement('div');
                    nd.setAttribute('mt-tag', t.id);                            
                    nd.setAttribute('id', id);      
        
                    if(t.di != ''){
                        el = w.mt_f(t.di, t.n, xd, t);
                    } else {
                        w.mt_log(t, 'Div ID is not defined, use BODY instead');
                        var el = xd.querySelector('body');
                    }

                    w.mt_log(t, 'Parent block:', el);

                    if(el){

                        w.mt_log(t, 'New div to add:',nd);
                        w.mt_ib(t, el, nd, t.append_type);

                    }

                    t.do = t.di;
                    t.di = id;

                    t.d = w.mt_f(t.di, t.n, xd, t);
                    
                    w.mt_log(t, 'Created block: ', t.d);

                    if(t.d){

                        if(t.css != ''){                            
                            t.d.setAttribute('style', t.css);                            
                        }                            

                        t.d.onmouseover = function(){
                            t.rm = true;
                        }

                        t.d.onmouseout = function(){
                            t.rm = false;
                            t.rs = 0;
                        }

                    } else {

                        t.a = false;
                        w.mt_log(t, 'Disactivate Tag (div not found)');
                        w.mt_send_error(t, 1, t.do);

                        if(t.s.length == 0){

                            if(!t.hasOwnProperty('reactivate')){                         
                                t.reactivate = true;
                            } else {
                                t.reactivate = false;                            
                            }

                            w.mt_log(t, 'Set reactivate',t.reactivate);

                        }

                    }
                    
                    t.da = true;

                } else {
                    
                    if(t.s.length > 0){
                        t.a = false;
                        w.mt_log(t, 'Disactivate Tag (div already created)');                    
                    }

                }

            }

        }

        if(t.a && (t.s.length > 0 || t.p.length > 0)){

                if(t.a) t.a = w.mt_d(t.sh);
                
                if(t.a){
                    if(t.g && t.c.length > 0){
                        t.a = t.c.includes(w.mt_4);
                        if(!t.a){                            
                            w.mt_log(t, 'Disactivate Tag (countries)');
                        }
                    }
                } else {
                    w.mt_log(t, 'Disactivate Tag (devices)');
                }

        } else {
            w.mt_log(t, 'Disactivate Tag (already found)');
            t.a = false;
        }

        w.mt_log_group(t,'PROCESS',false);
            
    });

    console.log(w.mt_t);
    
    w.mt_log(false, '> check countries rest');

    w.mt_t.forEach(function(t){
        if(t.a && (t.s.length > 0 || t.p.length > 0)){
            if(t.g && t.c.length == 0){
                w.mt_t.forEach(function(ti) {
                    if(ti.di == t.di && ti.g && ti.c.length > 0 && t.a){
                        t.a = false;
                        w.mt_log(t, '> Disactivate Tag (countries)');                    
                    }
                });
            }
        }
    });

    w.mt_log(false, '> rotation groups');

    var groups = new Array(); 

    w.mt_t.forEach(function(t){
        if(t.a && t.rg){
            if(!Array.isArray(groups[t.rg])){
                groups[t.rg] = new Array();
            }
            groups[t.rg].push(t);
        }
    });

    groups.forEach(function(group){

        w.mt_log(false,'-- GROUP --', group);
        var stay =  Math.floor(Math.random() * (group.length + 1));
        w.mt_log(false,'To stay', stay);

        for(var i = 0; i < group.length;i++){
            if(i!=stay){
                group[i].a = false;
                w.mt_log(group[i], '> Disactivate Tag (group)');
            }
        }
        
        
    });



    w.mt_log(false, '> before activate');
    console.log(w.mt_t);

    w.mt_6 = true;

    w.mt_t.forEach(function(t){

        w.mt_log(t, '> ACTIVATE = ', (t.a && !t.ch));

        if(t.a && !t.ch){

            if(t.clb){                
                w.mt_sc(t,t.d_parent);
            }

            w.mt_s(t);
        }

        w.mt_log(t, '> REACTIVATE = ', t.reactivate);

        if(!t.reactivate){
            t.ch = true;
        } else {
            w.mt_log(t, '> SET REACTIVATE TIMER');
            clearInterval(w.mt_5);
            w.mt_5 = setTimeout(function(){
                w.mt_log(false, '> START REINIT TIMER');
                w.mt_pt();
            },3000);
        }

    });

}

w.mt_s = function(t){

    t.rs = 0;
    t.ls = 0;    

    w.mt_log(t, '> call start tag');
    w.mt_e(t,t.s,false);
    w.mt_log(t, '> set first run false');
    t.f = false;

}

w.mt_spt = function(t, statistick_only){

    w.mt_log(t,'PASSBACK DELAY '+t.pd);
    w.mt_log(t, 'SET CUSTOM PASSBACK time '+(3000 + t.pd), null, 'passback');

    if(t.s.length == 0 && t.pa && t.ls == 0){
        w.mt_log(t,'SET SHORT TIME');
    }

    var timeout = ((t.s.length == 0 && t.pa && t.ls == 0)?50:(3000 + t.pd));

    w.setTimeout(function(){

        w.mt_log(t, '--== start custom passback ==--',null,'passback,bold');
                
        if(t.s.length == 0 && t.pa && t.ls == 0){
            w.mt_log(t, 'simple mode', null, 'passback');
        }        

        if(t.pf && t.clp){
            w.mt_sc(t,t.d);
        }

        w.mt_l_result_found = false;

        if(t.pf && (t.ou || t.s.length == 0)){

            w.mt_log(t,'CHECK OUTER',true, 'passback');
            w.mt_log_group(t,'PB LOOKER',true);
            w.mt_l(t, t.d_parent);
            w.mt_log_group(t,'PB LOOKER',false);

        } else {

            w.mt_log(t,'CHECK INNER',true, 'passback');
            w.mt_log_group(t,'PB LOOKER',true);
            w.mt_l(t, t.d);
            w.mt_log_group(t,'PB LOOKER',false);

        }
        
        white = !w.mt_l_result_found; 
        var white_real = white;

        if(t.s.length == 0 && t.pa && t.ls == 0){

            w.mt_log_group(t,'Fake white',true, 'passback');
            white = true;

        }

        w.mt_log(t, '>>> White ', white, 'passback,bold');
        w.mt_p({
                t: t, 
                white: white,
                white_real: white_real,
                direct: false, 
                statistick_only: statistick_only
            }
        );
        w.mt_log(t, '>>> --== end custom passback ==--', null, 'passback,bold');

        t.pf = false;

    },timeout);

// && t.ls != 0 

}

w.mt_p = function(obj){

    w.mt_log(t, '>>> start passback execution', obj, 'passback,bold');

    var t = obj.t;
    var white = obj.white;
    if(obj.white_real){
        var white_real = obj.white_real;
    } else {
        var white_real = white;
    }
    var direct = obj.direct;
    if(obj.statistick_only){
        var statistick_only = obj.statistick_only;
    } else {
        var statistick_only = false;
    }

    w.mt_log(t, '--== passback function ==--', statistick_only, 'passback,bold');

    this.mt_st(t, 1, white_real);

    if(white && !statistick_only){

        t.rs = 0;       
        
        if(t.pb){

            var style = w.getComputedStyle(t.d, null);
            var height = style.getPropertyValue('height');     
            
            if(!t.dont_fix_height){
                t.d.style.minHeight = height;
            } else {

                w.mt_log(t, 'compute max passback height');

                height = 0;

                t.p.forEach(function(p){
                    if(p.h == 0){
                        if(p._h > height){
                            height = p._h;
                        }
                    }
                });

                w.mt_log(t, 'computed height: ', height);

                t.d.style.minHeight = height;

            }

            t.pa = true;

            if(t.p.length > 0){

                w.mt_log(t, 'Before clear', t.d, 'passback');
                t.d.innerHTML = '';
                w.mt_log(t, 'Block cleared', t.d, 'passback');

                var call_with_timer = false;

                //avoid cycle bug with headerbidding in passback after headerbidding

                if(direct){

                    var call_after_passback = false;
                    var have_parent_id = false;

                    for(var i = 0;i<t.p.length;i++){

                        if(t.p[i].pid!=0){
                            have_parent_id = true;
                        }

                        if(t.p[i].id==t.ls){
                            call_after_passback = true;
                        }

                    }

                    if(call_after_passback && !have_parent_id){
                        call_with_timer = true;
                    }

                    w.mt_log(t, 'Call after passback:', call_after_passback, 'passback');
                    w.mt_log(t, 'Have parent id:', have_parent_id, 'passback');
                    w.mt_log(t, 'Call with timer:', call_with_timer, 'passback');

                }

                if(!call_with_timer){
                    w.mt_log(t, 'Call immediately', null, 'passback');
                    w.mt_e(t, t.p, true);                    
                } else {
                    w.mt_log(t, 'Call with timer', null, 'passback');
                    w.mt_spt(t);
                }

            }

        }

    } else {
        w.mt_log(t, 'do nothing', null, 'passback,bold');
    }

}

w.mt_e = function(t,fns,is_passback){

    var statistick_only = false;

    if(fns.length == 0) {

        //if passback ony tag

        if(!is_passback){
            w.mt_log(t, 'Call pb timer 1', null, 'passback');
            w.mt_spt(t);
        }

        return 0;
    }
    
    w.mt_log(t, '> call exec function last_id = '+t.ls+' passback ' + is_passback, null, 'execute,bold');

    var tfr = 0;
    var cfr = 0;
    var r = Math.random();
    var fns_fin = [];
    var have_parent_id = false;

    for(var i = 0; i< fns.length; i++){
        if(fns[i].pid!=0){
            have_parent_id = true;
        }
    }

    if(have_parent_id){

        var after_primary = false;

        for(var i = 0;i<t.s.length;i++){
            if(t.s[i].id == t.ls){
                after_primary = true;
            }
        }

        w.mt_log(t, 'After primary: ', after_primary, 'execute');    

        //If found a function ierarchy

        for(var i = 0; i< fns.length; i++){

            w.mt_log(t, 'Script pid = ' + fns[i].pid + '/' + t.ls, null, 'execute');       

            if((fns[i].pid == t.ls) && (fns[i].id != t.ls) || (after_primary && fns[i].pid == 0)){                
                w.mt_log(t, 'Add', null, 'execute');
                fns_fin.push(fns[i]);
            }

        }

    } else {

        //Functions have no 

        fns_fin = fns;

        if(is_passback){
            statistick_only = true;
        }

    }

    w.mt_log(t, 'Statistick only', statistick_only, 'execute');    
    w.mt_log(t, 'fns_fin', fns_fin, 'execute');

    for(var i = 0; i < fns_fin.length; i++){
        tfr += fns_fin[i].fr;        
    }

    for(var i = 0; i < fns_fin.length; i++){
        w.mt_log(t, 'frtoadd = '+fns_fin[i].fr+' / '+(fns_fin[i].fr/tfr), null, 'execute');
    }

    w.mt_log(t,'tfr = ' + tfr, null, 'execute');
    w.mt_log(t,'random = ' + r, null, 'execute');

    var i = 0;
    
    fns_fin.some(function(f){

        i++;
        cfr += f.fr / tfr;        
        w.mt_log(t,i+') cfr = '+cfr+' r= '+r, null, 'execute');
        if(cfr >= r){            

            w.mt_log(t,'Start function ' + i, null, 'execute');

            t.ls = f.id;

            t.d.setAttribute('mt-ls', t.ls);
            t.d.setAttribute('mt-pb', is_passback?1:0);
            t.d.setAttribute('mt-hb', f.h);

            if(f.h == 0){

                w.mt_log(t,'Start custom function', null, 'execute');

                try {
                    f.fn(t);            
                } catch(e){
                    w.mt_send_error(t, 10, e.name + ":" + e.message);
                }

                w.mt_log(t,'Start passback timer, stat only', statistick_only, 'execute');                    
                w.mt_spt(t, statistick_only)                

            } else {

                w.mt_log(t,'Start headerbidding', null, 'execute');                
                w.mt_h(t,f);

            }              

            return true;
            
        }
    });

    if(fns_fin.length == 0 && is_passback && have_parent_id){
        //passback statistick for last script in cascade
        w.mt_log(t, 'Call pb timer, nu functions found', null, 'passback');
        w.mt_spt(t, true);

    }

}
            })(window, document,performance);</script>