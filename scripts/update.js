/**
 * Provides an AJAX-update mechanism for the calculator
 **/
var request;
var timeout;
var changed;
var wait;
var path;
setup();
function setup() {
  path=new Array();
  var strGET=document.location.search.substr(1,document.location.search.length);
  if(strGET!='') {
    gArr=strGET.split('&');
    for(i=0;i<gArr.length;++i) {
      v='';
      vArr=gArr[i].split('=');
      if(vArr.length>1){v=vArr[1];}
      path[unescape(vArr[0])]=unescape(v);
    }
  }
  var counter = 0;
  changed = new Array();
  var inputs = document.getElementsByTagName('input');
  for(counter = 0;counter < inputs.length;counter++) {
    if(inputs[counter].getAttribute('type')=='submit') {
      inputs[counter].removeAttribute('id');
      inputs[counter].removeAttribute('name');
    } else if(inputs[counter].getAttribute('type')!='checkbox') {
      var pre = inputs[counter].getAttribute('onchange');
      if(pre==null) {
        pre = '';
      }
      inputs[counter].setAttribute('onchange', pre+'prepareRequest(this.value,this.id)');
    }
  }
  var inputs = document.getElementsByTagName('button');
  for(counter = 0;counter < inputs.length;counter++) {
    if(inputs[counter].getAttribute('type')=='submit') {
      inputs[counter].removeAttribute('id');
      inputs[counter].removeAttribute('name');
    } else if(inputs[counter].getAttribute('type')!='checkbox') {
      var pre = inputs[counter].getAttribute('onchange');
      if(pre==null) {
        pre = '';
      }
      inputs[counter].setAttribute('onchange', pre+'prepareRequest(this.value,this.id)');
    }
  }
  //HTTP-Request
  try{
    //most browsers
    request=new XMLHttpRequest();
  } catch(e) {
    //Old IEs
    request =new ActiveXObject("Microsoft.XMLHTTP");
  }
  var div = document.createElement('div');
  div.setAttribute('style', 'display:none');
  div.setAttribute('id', 'sand-clock');
  var img = document.createElement('img');
  img.setAttribute('alt', 'Please wait');
  img.setAttribute('src', 'sand-clock.gif');
  img.setAttribute('style', 'width:100%');
  document.getElementsByTagName('body')[0].appendChild(div).appendChild(img);
  var path_div= document.createElement('div');
  path_div.setAttribute('class', 'link-to-button');
  path_div.setAttribute('id', 'path');
  var link= document.createElement('a');
  link.setAttribute('href', document.URL);
  var text = document.createTextNode('current URL');
  document.getElementsByTagName('h1')[0].parentNode.insertBefore(path_div,document.getElementsByTagName('h1')[0].nextSibling).appendChild(link).appendChild(text);
  setUrl();
}
function getBodyWidth() {
  var margin = window.getComputedStyle(document.getElementsByTagName('body')[0]).getPropertyValue('width');
  margin = margin.substring(0,margin.length-2)
  return parseInt(margin);
}
function prepareRequest(value,id) {
  try{
    window.clearTimeout(changed[id]);
  } catch(e) {
    //no timeout yet, fine
  }
  changed[id] = window.setTimeout(function() {sendRequest(value,id);},250)
}
function sandClock(use) {
  if(document.getElementById('sand-clock').getAttribute('style')=='display:none') {
    if(!use) {
      wait = window.setTimeout(function() {sandClock(true);},50);
    } else {
      var bodyWidth = getBodyWidth();
      if(bodyWidth<128) {
        document.getElementById('sand-clock').setAttribute('style','width:'+(bodyWidth-4)+'px;width:'+(bodyWidth-4)+'px;overflow:hidden;left:'+(bodyWidth/2-64)+'px;top:50%;position:absolute;');
      } else {
        document.getElementById('sand-clock').setAttribute('style','width:auto;height:auto;overflow:hidden;left:'+(bodyWidth/2-64)+'px;top:50%;position:absolute;');
      }
    }
  } else {
    window.clearTimeout(wait);
    document.getElementById('sand-clock').setAttribute('style','display:none');
  }
}
function sendRequest(value,id) {
  path[id]=value;
  sandClock();
  var shared = 'false';
  try {
    if(document.getElementById(id).getAttribute('data-ajax') == 'shared') {
        shared = '1';
    }
  } catch(e) {
      try {
          if(document.getElementById(id).dataset.ajax == 'shared') {
              shared = '1';
          }
      } catch(f) {}
  }
  var query='http://'+window.location.hostname+'/'+'ajax.xml?'+id+'='+value+'&asXML=true&shared='+shared;
  request.onreadystatechange = function() {recieveAnswer(false);};
  timeout = window.setTimeout(function() {recieveAnswer(true);},5000);
  request.open("GET",query,true);
  request.send(null);
}
function setUrl() {
  var buildPath = window.location.protocol+'//'+window.location.host+window.location.pathname+'?';
  for(var key in path) {
    buildPath = buildPath+'&'+key+'='+path[key];
  }
  buildPath = buildPath.replace(/\?[^ad]*\&/,'?');
  document.getElementById('path').firstChild.setAttribute('href', buildPath);
  window.history.pushState("","", buildPath);
}
function recieveAnswer(paththrough) {
  if(request.readyState == 4/* && request.status==200*/) {
    var values = request.responseXML;
    if(values !== null && typeof(values) =='object') {
      setUrl();
      sandClock();
      window.clearTimeout(timeout);
      for(var counter = 0;counter < values.documentElement.childNodes.length;counter++) {
        if(values.documentElement.childNodes[counter].nodeType != 3) {
          var id= values.documentElement.childNodes[counter].getAttribute('name');
          var el;
          var img=values.documentElement.childNodes[counter].getAttribute('compare');
          var value = values.documentElement.childNodes[counter].firstChild.data;
          try {
            el=window.document.getElementById(id);
          } catch(e) {
            //nothing
          }
          if(el) {
            el.firstChild.data = value;
            try {
              if(img==0) {
                el.parentNode.lastChild.innerText = '=';
              } else if(img==-1) {
                el.parentNode.lastChild.innerText = '↓';
              } else if(img==1) {
                el.parentNode.lastChild.innerText = '↑';
              }
            } catch(f) {
              //nothing
            }
          }
        }
      }
    }
  }else if(paththrough == true){
    window.clearTimeout(timeout);
    sandClock();
    alert('Server connection couldn\'t be established. Please reload the webpage.');
  }
}