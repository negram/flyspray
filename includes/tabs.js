/*
 * $Id: tabs.js 805 2004-12-23 00:02:21Z cr $
 */
addEvent(window, "load", initTabs);
function addEvent(elm, evType, fn, useCapture)
// addEvent and removeEvent
// cross-browser event handling for IE5+,  NS6 and Mozilla
// By Scott Andrew
{
  if (elm.addEventListener){
    elm.addEventListener(evType, fn, useCapture);
    return true;
  } else if (elm.attachEvent){
    var r = elm.attachEvent("on"+evType, fn);
    return r;
  } else {
    alert("Handler could not be removed");
  }
} 

var _TAB_DIVS;

// cookies {{{
function createCookie(name,value,days) {
    if (days) {
	var date = new Date();
	date.setTime(date.getTime()+(days*24*60*60*1000));
	var expires = "; expires="+date.toGMTString();
    } else {
	expires = "";
    }
    document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
	var c = ca[i];
	while (c.charAt(0)==' ') c = c.substring(1,c.length);
	if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
// }}}

// tabs handling {{{
// show tab with given id
function showTabById(tabid) { // {{{
  var divs = document.getElementsByTagName('div');
  var tab = document.getElementById(tabid);
  var submenu = document.getElementById('submenu');
  var i;

  // ukryj wszystkie zakładki
  for (i=0; i<divs.length; i++) {
    if (divs[i].className && (divs[i].className.indexOf('tab') > -1)) {
      divs[i].style.display = 'none';
    }
  }
  // pokaż żądaną zakładkę
  if (tab) {
    tab.style.display = 'block';
    // ustaw klasę 'active' dla wywojącego elementu
    if (submenu) {
      links = submenu.getElementsByTagName('a');
      for (i=0; i<links.length; i++) { 
	if (links[i].href.match('^.*#'+tabid+'$')) {
	  links[i].className = 'active'; 
	} else { links[i].className = ''; }
      }
    }
  }
  if (window.scrollTo(0,0)) {
      window.scrollTo(0,0);
  }
} // }}}

// create JavaScript calls to switch tabs
function makeTabLinks() { // {{{
  var submenu = document.getElementById('submenu');
  var links, i, target;
 
  if (submenu) {
    links = submenu.getElementsByTagName('a');
    for (i=0; i<links.length; i++) {
      var href = links[i].getAttribute('href');
      target = href.substring(href.indexOf('#')+1);
      links[i]['onclick'] = new Function("showTabById('"+target+"'); return true;");
    }
  }
} // }}}

// show tab with given number
function showTabByNumber(number) { // {{{
  var targets = new Array();    // tab names
  var divs = document.getElementsByTagName('div');
  var i;

  for (i=0; i<divs.length; i++) {
    if (divs[i].className == 'tab') {
      targets.push(divs[i].id);
    }
  }
  if (number >= targets.length) {
    number = targets.length-1;      
  }
  showTabById(targets[number]);
} // }}}

// get list of all DIVs that contain tabs
function getTabDivs() {/*{{{*/
    if (_TAB_DIVS == null) {
	_TAB_DIVS = new Array();
	var divs = document.getElementsByTagName('div');
	var i;
	for (i=0; i<divs.length; i++) {
	    if (divs[i].className && (divs[i].className.indexOf('tab') > -1)) {
		_TAB_DIVS.push(divs[i]);
	    }
	}
    }
    return _TAB_DIVS;
}/*}}}*/

// tabs init
// show first tab or tab with given name (string after #)
function initTabs() {/*{{{*/
  var target = location.href.substring(location.href.indexOf('#')+1);
  makeTabLinks();

  if (target && document.getElementById(target)) {
    showTabById(target);
  } else {
    showTabByNumber(0);
  }

}/*}}}*/

// }}}

// vim:enc=utf-8:fenc=utf-8:fdm=marker
