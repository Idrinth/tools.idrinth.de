if(!document.cookie.match(/(^|;)\s*idrinth-cookie-accept=/)) {
    window.setTimeout(
            function(){
                var styles=document.createElement ("style");
                styles.innerHTML="#idrinth-cookie-accept {background:#fff;position:fixed;top:5px;right:5px;border:1px solid #000;box-shadow: 3px 3px 3px rgba(0,0,0,0.4);border-radius:3px;padding:2px}"
                                 +"#idrinth-cookie-accept > p,#idrinth-cookie-accept > ul{display:none}"
                                 +"#idrinth-cookie-accept:hover > p,#idrinth-cookie-accept:hover > ul{display:block}"
                                 +"#idrinth-cookie-accept > * {padding:0 0.2em;margin:0;}"
                                 +"#idrinth-cookie-accept > ul {padding-left:2em;}";
                var wrapper=document.createElement('div');
                wrapper.setAttribute("id","idrinth-cookie-accept");
                var content=document.createElement('h2');
                content.appendChild(document.createTextNode("Cookie Usage"));
                wrapper.appendChild(content);
                var content=document.createElement('p');
                content.appendChild(document.createTextNode("This website uses cookies like pretty much any other website. They fullfill the following tasks here:"));
                wrapper.appendChild(content);
                var content=document.createElement('ul');
                content.appendChild(document.createElement('li'));
                content.lastChild.appendChild(document.createTextNode("Tracking cookies for piwik help determine what parts of the site are used"));
                content.appendChild(document.createElement('li'));
                content.lastChild.appendChild(document.createTextNode("Session&Login cookies are used to keep the data you entered avaible"));
                wrapper.appendChild(content);
                var content=document.createElement('p');
                content.appendChild(document.createTextNode("None of this data is transfered to another party or other server, but all of it is meant to help direct my developement focus or make services avaible at all."));
                wrapper.appendChild(content);
                var content=document.createElement('p');
                content.appendChild(document.createTextNode("By using this website it is assumed, that you understood why cookies are used here and accept it."));
                wrapper.appendChild(content);
                var content=document.createElement('button');
                content.appendChild(document.createTextNode("Close & Accept"));
                content.onclick=function(){
                    date= new Date();
                    date.setFullYear(date.getFullYear()+1);
                    document.cookie="idrinth-cookie-accept=1; expires="+date.toUTCString ()+"; path=/";
                    document.getElementsByTagName ("body")[0].removeChild(document.getElementById("idrinth-cookie-accept"));
                };
                wrapper.appendChild(content);
                document.getElementsByTagName ("body")[0].appendChild(wrapper);
                document.getElementsByTagName ("head")[0].appendChild(styles);
            },250);
}