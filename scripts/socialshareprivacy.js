var body = document.getElementsByTagName('body')[0];
var media = {
    'facebook':{
        'button' : null,
        'code' : null,
    },
    'twitter':{
        'button' : null,
        'code' : null,
    },
};
/*
 * socialshareprivacy.js | 2 Klicks fuer mehr Datenschutz
 *
 * based on:
 * http://www.heise.de/extras/socialshareprivacy/
 * http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html
 *
 * Copyright (c) 2011 Hilko Holweg, Sebastian Hilbig, Nicolas Heiringhoff, Juergen Schmidt,
 * Heise Zeitschriften Verlag GmbH & Co. KG, http://www.heise.de
 *
 * is released under the MIT License http://www.opensource.org/licenses/mit-license.php
 *
 * Spread the word, link to us if you can.
 */
// abbreviate at last blank before length and add "\u2026" (horizontal ellipsis)
function abbreviateText(text, length) {
    var abbreviated = decodeURIComponent(text);
    if (abbreviated.length <= length) {
        return text;
    }
    var lastWhitespaceIndex = abbreviated.substring(0, length - 1).lastIndexOf(' ');
    abbreviated = encodeURIComponent(abbreviated.substring(0, lastWhitespaceIndex)) + "\u2026";
    return abbreviated;
}

// returns content of <meta name="" content=""> tags or '' if empty/non existant
function getMeta(name) {
    var count = 0;
    var meta = document.getElementsByTagName('meta');
    for(count = 0;count<meta.length;count++) {
        if(meta[count].getAttribute('name')===name) {
            return meta[count].getAttribute('content');
        }
    }
    return '';
}
    
// create tweet text from content of <meta name="DC.title"> and <meta name="DC.creator">
// fallback to content of <title> tag
function getTweetText() {
    var title = document.getElementsByTagName('title')[0].firstChild.nodeValue;
    return encodeURIComponent(title+':'+getMeta('description'));
}

// build URI from rel="canonical" or document.location
function getURI() {
    var uri = document.location.href.substring(0,document.location.href.indexOf('?'));
    if(uri==='') {
         uri = document.location.href;
    }
    return uri;
}
function switchTurner(father) {
    var father_element = document.getElementById(father);
    if (father_element.childNodes[0].getAttribute('class').match(/off/).length  > 0) {
        father_element.setAttribute('class',father_element.setAttribute('class',father_element.getAttribute('class').replace(/info_on/,'info_off')));
        father_element.childNodes[0].setAttribute('class',father_element.childNodes[0].getAttribute('class').replace(/off/,'on'));
        father_element.childNodes[1].removeChild(media[father].button);
        if(media[father].code.length === undefined||media[father].code.length!==2) {                
            father_element.childNodes[1].appendChild(media[father].code); 
        } else {
            father_element.childNodes[1].appendChild(media[father].code[0]);
            father_element.childNodes[1].appendChild(media[father].code[1]);                
        }
    } else {
        father_element.setAttribute('class',father_element.setAttribute('class',father_element.getAttribute('class').replace(/info_off/,'info_on')));
        father_element.childNodes[0].setAttribute('class',father_element.childNodes[0].getAttribute('class').replace(/on/,'off'));
        if(media[father].code.length === undefined||media[father].code.length!==2) {                
            father_element.childNodes[1].removeChild(media[father].code);
        } else {
            father_element.childNodes[1].removeChild(media[father].code[0]);
            father_element.childNodes[1].removeChild(media[father].code[1]);                
        }            
        father_element.childNodes[1].appendChild(media[father].button);
    }
}
function socialSharePrivacy() {
    var options = {
        'services' : {
            'facebook' : {
                'status'            : 'on',
                'dummy_img'         : 'dummy_facebook.png',
                'perma_option'      : 'on',
                'display_name'      : 'Facebook',
                'referrer_track'    : '',
                'language'          : 'en_GB',
                'action'            : 'recommend'
            }, 
            'twitter' : {
                'status'            : 'on', 
                'dummy_img'         : 'dummy_twitter.png',
                'perma_option'      : 'on',
                'display_name'      : 'Twitter',
                'referrer_track'    : '', 
                'tweet_text'        : getTweetText,
                'language'          : 'en'
            },
        },
        'cookie_path'       : '/',
        'cookie_domain'     : document.location.host,
        'cookie_expires'    : '365',
        'txt_help'          : 'Due to requirements of the german laws, these buttons will only load the necessary scripts after the first click. To use them please click twice. This prevents the related networks from tracking your surf history.',
        'uri'               : getURI
    };
    var facebook_on = (options.services.facebook.status === 'on');
    var twitter_on  = (options.services.twitter.status  === 'on');

    // check if at least one service is "on"
    if (!facebook_on && !twitter_on && !gplus_on) {
        return;
    }
    var father = document.getElementById('social');
    var child = document.createElement('ul');
    child.setAttribute('class','social_share_privacy_area');
    father.appendChild(child);
    father=child;
    // canonical uri that will be shared
    var uri = options.uri;
    if (typeof uri === 'function') {
        uri = uri();
    }

    //
    // Facebook
    //
    if (facebook_on) {
        var fb_enc_uri = encodeURIComponent(uri + options.services.facebook.referrer_track);
        media.facebook.code = document.createElement('iframe');
        media.facebook.code.setAttribute('src','http://www.facebook.com/plugins/like.php?locale=' + options.services.facebook.language + '&href=' + fb_enc_uri + '&send=false&layout=button_count&width=120&show_faces=false&action=' + options.services.facebook.action + '&colorscheme=light&font&height=21');
        media.facebook.code.setAttribute('scrolling','no');
        media.facebook.code.setAttribute('frameborder','0');
        media.facebook.code.setAttribute('style','border:none; overflow:hidden; width:145px; height:20px;');
        media.facebook.code.setAttribute('allowTransparency','true');
        media.facebook.button = document.createElement('img');
        media.facebook.button.setAttribute('src',options.services.facebook.dummy_img);
        media.facebook.button.setAttribute('alt','Facebook Like');
        media.facebook.button.setAttribute('class','fb_like_privacy_dummy');
        media.facebook.button.setAttribute('style','border:none; overflow:hidden; width:94px; height:21px;');
        media.facebook.button.setAttribute('onclick','switchTurner(\'facebook\')');

        child = document.createElement('li');
        child.setAttribute('class','facebook help_info');
        child.setAttribute('id','facebook');
        //switch
        grandchild = document.createElement('span');
        grandchild.setAttribute('class','switch off');
        grandchild.setAttribute('onclick','switchTurner(\'facebook\')');
        child.appendChild(grandchild);
        //button
        grandchild = document.createElement('div');
        grandchild.setAttribute('class','fb_like dummy_btn');
        grandchild.appendChild(media.facebook.button);
        child.appendChild(grandchild);
        //add to ul
        father.appendChild(child);
    }

    //
    // Twitter
    //
    if (twitter_on) {
        var text = options.services.twitter.tweet_text;
        if (typeof text === 'function') {
            text = text();
        }
        // 120 is the max character count left after twitters automatic url shortening with t.co
        text = abbreviateText(text, '100');
        var twitter_enc_uri = encodeURIComponent(uri + options.services.twitter.referrer_track);
        var twitter_count_url = encodeURIComponent(uri);

        media.twitter.code = document.createElement('iframe');
        media.twitter.code.setAttribute('src','http://platform.twitter.com/widgets/tweet_button.html?url=' + twitter_enc_uri + '&counturl=' + twitter_count_url + '&text=' + text + '&count=horizontal&lang=' + options.services.twitter.language);
        media.twitter.code.setAttribute('scrolling','no');
        media.twitter.code.setAttribute('frameborder','0');
        media.twitter.code.setAttribute('style','border:none; overflow:hidden; width:145px; height:20px;');
        media.twitter.code.setAttribute('allowTransparency','true');
        media.twitter.button = document.createElement('img');
        media.twitter.button.setAttribute('src',options.services.twitter.dummy_img);
        media.twitter.button.setAttribute('alt','Tweet this');
        media.twitter.button.setAttribute('class','tweet_this_dummy');
        media.twitter.button.setAttribute('style','border:none; overflow:hidden; width:55px; height:20px;');
        media.twitter.button.setAttribute('onclick','switchTurner(\'twitter\')');

        child = document.createElement('li');
        child.setAttribute('class','twitter help_info');
        child.setAttribute('id','twitter');
        //switch
        grandchild = document.createElement('span');
        grandchild.setAttribute('class','switch off');
        grandchild.setAttribute('onclick','switchTurner(\'twitter\')');
        child.appendChild(grandchild);
        //button
        grandchild = document.createElement('div');
        grandchild.setAttribute('class','tweet dummy_btn');
        grandchild.appendChild(media.twitter.button);
        child.appendChild(grandchild);
        //add to ul
        father.appendChild(child);
    }

    //
    // Der Info/Settings-Bereich wird eingebunden
    //
    var child = document.createElement('li');
    child.setAttribute('class','settings_info');
    child.setAttribute('onmouseenter','showHelp();');
    child.setAttribute('onmouseleave','hideHelp()');        
    child.setAttribute('onclick','showHelp()');
    grandchild = document.createElement('span');
    grandchild.setAttribute('class','help_info icon');

    child.appendChild(grandchild);
    //add to ul
    father.appendChild(child);

    var child = document.createElement('div');
    child.setAttribute('id','social-info');
    child.appendChild(document.createTextNode(options.txt_help));
    body.appendChild(child);
}
function showHelp() {
    window.setTimeout(function () {
        document.getElementById('social').getElementsByTagName('li')[3].setAttribute('onclick','hideHelp()');
        document.getElementById('social-info').setAttribute('style','display:block;');
        var xy = getXYpos(document.getElementById('social').getElementsByTagName('li')[3]);
        xy['y'] = xy['y']-document.getElementById('social-info').offsetHeight;
        if(xy['x']+220 > body.clientWidth) {
            xy['x'] = parseInt((body.clientWidth - 220)/2);
        }
        if(xy['y']<0) {
            xy['y'] = 0;
        }
        if(xy['x']<0) {
            xy['x'] = 0;
        }
        document.getElementById('social-info').setAttribute('style','display:block;left:'+xy['x']+'px;top:'+xy['y']+'px;');
    }, 500);
}
function hideHelp() {
    window.setTimeout(function () {        
        document.getElementById('social').getElementsByTagName('li')[3].setAttribute('onclick','showHelp()');
        document.getElementById('social-info').setAttribute('style','');
    }, 500);
}
function getXYpos(elem) {
    if (!elem) {
        return {"x":0,"y":0};
    }
    var xy={"x":elem.offsetLeft,"y":elem.offsetTop}
    var par=getXYpos(elem.offsetParent);
    for (var key in par) {
        xy[key]+=par[key];
    }
    return xy;
}
socialSharePrivacy();
