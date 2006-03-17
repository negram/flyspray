// Set up the error/success bar fader
addEvent(window,'load',setUpFade);
// Set up the task list onclick handler
addEvent(window,'load',setUpTasklistTable);
function Disable(formid)
{
   document.formid.buSubmit.disabled = true;
   document.formid.submit();
}

function openTask( url )
{
   window.location = url;
}

 function showstuff(boxid){
   document.getElementById(boxid).style.visibility="visible";
   document.getElementById(boxid).style.display="block";
}

function hidestuff(boxid){
   document.getElementById(boxid).style.visibility="hidden";
   document.getElementById(boxid).style.display="none";
}

function showhidestuff(boxid) {
   switch (document.getElementById(boxid).style.visibility) {
      case '': document.getElementById(boxid).style.visibility="visible"; break
      case 'hidden': document.getElementById(boxid).style.visibility="visible"; break
      case 'visible': document.getElementById(boxid).style.visibility="hidden"; break
   }
   switch (document.getElementById(boxid).style.display) {
      case '': document.getElementById(boxid).style.display="block"; break
      case 'none': document.getElementById(boxid).style.display="block"; break
      case 'block': document.getElementById(boxid).style.display="none"; break
      case 'inline': document.getElementById(boxid).style.display="none"; break
   }
}

function setUpFade() {
  if (document.getElementById('errorbar')) {
    elName = 'errorbar';
  } else if (document.getElementById('successbar')) {
    elName = 'successbar';
  } else {
    return;
  }
  fader(elName,2000,50,2500);
}
// Fades an element
// elName - id of the element
// start - time in ms when the fading should start
// steps - number of fading steps
// time - the length of the fade in ms
function fader(elName,start,steps,time) {
  setOpacity(elName,100); // To prevent flicker in Firefox
                          // The first time the opacity is set
                          // the element flickers in Firefox
  fadeStep = 100/steps;
  timeStep = time/steps;
  opacity = 100;
  time = start + 100;
  while (opacity >=0) {
    window.setTimeout("setOpacity('"+elName+"',"+opacity+")",time);
    opacity -= fadeStep;
    time += timeStep;
  }
}
function setOpacity(elName,opacity) {
  opacity = (opacity == 100)?99:opacity;
  el = document.getElementById(elName);
  // IE
  el.style.filter = "alpha(opacity:"+opacity+")";
  // Safari < 1.2, Konqueror
  el.style.KHTMLOpacity = opacity/100;
  // Old Mozilla
  el.style.MozOpacity = opacity/100;
  // Safari >= 1.2, Firefox and Mozilla, CSS3
  el.style.opacity = opacity/100
}
function setUpTasklistTable() {
  if (!document.getElementById('tasklist_table')) {
    // No tasklist on the page
    return;
  }
  var table = document.getElementById('tasklist_table');
  addEvent(table,'click',tasklistTableClick);
}
function tasklistTableClick(e) {
  var src = eventGetSrc(e);
  if (src.nodeName != 'TD') {
    return;
  }
  if (src.hasChildNodes()) {
    var checkBoxes = src.getElementsByTagName('input');
    if (checkBoxes.length > 0) {
      // User clicked the cell where the task select checkbox is
      if (checkBoxes[0].checked) {
        checkBoxes[0].checked = false;
      } else {
        checkBoxes[0].checked = true;
      }
      return;
    }
  }
  var row = src.parentNode;
  var aElements = row.getElementsByTagName('A');
  if (aElements.length > 0) {
    window.location = aElements[0].href;
  } else {
    // If both the task id and the task summary columns are non-visible
    // just use the good old way to get to the task
    window.location = '?do=details&id=' + row.id.substr(4);
  }
}

function eventGetSrc(e) {
  if (e.target) {
    return e.target;
  } else if (window.event) {
    return window.event.srcElement;
  } else {
    return;
  }
}

function ToggleSelectedTasks() {
  var inputs = document.getElementById('massops').getElementsByTagName('input');
  for (var i = 0; i < inputs.length; i++) {
    if(inputs[i].type == 'checkbox'){
      inputs[i].checked = !(inputs[i].checked);
    }
  }
  // Return false to prevent the the browser from following the href
  return false;
}

function addUploadFields() {
  var el = document.getElementById('uploadfilebox');
  var span = el.getElementsByTagName('span')[0];
  if ('none' == span.style.display) {
    // Show the file upload box
    span.style.display = 'inline';
    // Switch the buttons
    document.getElementById('attachafile').style.display = 'none';
    document.getElementById('attachanotherfile').style.display = 'inline';
    
  } else {
    // Copy the first file upload box and clear it's value
    var newBox = span.cloneNode(true);
    newBox.getElementsByTagName('input')[0].value = '';
    el.appendChild(newBox);
  }
}
function removeUploadField(element) {
  var el = document.getElementById('uploadfilebox');
  var span = el.getElementsByTagName('span');
  if (1 == span.length) {
    // Clear and hide the box
    span[0].style.display='none';
    span[0].getElementsByTagName('input')[0].value = '';
    // Switch the buttons
    document.getElementById('attachafile').style.display = 'inline';
    document.getElementById('attachanotherfile').style.display = 'none';
  } else {
    el.removeChild(element.parentNode);
  }
}

function updateDualSelectValue(id)
{
    var rt  = document.getElementById('r'+id);
    var val = document.getElementById('v'+id);

    val.value = '';

    var i;
    for (i=0; i < rt.options.length; i++) {
        val.value += ' ' + rt.options[i].value;
    }
}

function dualSelect(from, to, id) {
    if (typeof(from) == 'string') {
        from = document.getElementById(from+id);
    }
    if (typeof(to) == 'string') {
        to = document.getElementById(to+id);
    }

    var i = 0;
    var opt;

    while (i < from.options.length) {
        if (from.options[i].selected) {
            opt = new Option(from.options[i].text, from.options[i].value);
            try {
                to.add(opt, null);
            }
            catch (ex) {
                to.add(opt);
            }
            from.remove(i);
            continue;
        }
        i++;
    }
    updateDualSelectValue(id);
}

function selectMove(id, step) {
    var sel = document.getElementById('r'+id);

    var i = 0;

    while (i < sel.options.length) {
        if (sel.options[i].selected) {
            if (i+step < 0 || i+step > sel.options.length) {
                return;
            }
            var opt = new Option(sel.options[i].text, sel.options[i].value);
            sel.remove(i);
            try {
                sel.add(opt, sel.options[i+step]);
            }
            catch (ex) {
                sel.add(opt, i+step);
            }

            opt.selected = true;

            updateDualSelectValue(id);
            return;
        }
        i++;
    }
}

function checknewtask(message)
{
	var itemsummary = document.getElementById("itemsummary").value;
	var details = document.getElementById("details").value;
	
	if(itemsummary == "" || details == "")
	{
		alert(message);
		return false;
	}
	return true;
}
var Cookie = {
  getVar: function(name) {
    var cookie = document.cookie;
    if (cookie.length > 0) {
      cookie += ';';
    }
    re = new RegExp(name + '\=(.*?);' );
    if (cookie.match(re)) {
      return RegExp.$1;
    } else {
      return '';
    }
  },
  setVar: function(name,value,expire,path) {
    document.cookie = name + '=' + value;
  },
  removeVar: function(name) {
    var date = new Date(12);
    document.cookie = name + '=;expires=' + date.toUTCString();
  }  
};
function setUpSearchBox() {
  if (document.getElementById('advancedsearch')) {
    var state = Cookie.getVar('advancedsearch');
    if ('1' == state) {
      var showState = document.getElementById('advancedsearchstate');
      showState.replaceChild(document.createTextNode('+'),showState.firstChild);
      document.getElementById('sc2').style.display = 'block';
    }
  }
}
function toggleSearchBox() {
  var state = Cookie.getVar('advancedsearch');
  if ('1' == state) {
      var showState = document.getElementById('advancedsearchstate');
      showState.replaceChild(document.createTextNode('+'),showState.firstChild);
      hidestuff('sc2');  
      Cookie.setVar('advancedsearch','0');
  } else {
      var showState = document.getElementById('advancedsearchstate');
      showState.replaceChild(document.createTextNode('-'),showState.firstChild);
      showstuff('sc2'); 
      Cookie.setVar('advancedsearch','1');
  }
}
function deletesearch(id, url) {
    var oNodeToRemove = document.getElementById('rs' + id)
    oNodeToRemove.parentNode.removeChild(oNodeToRemove);
    var table = document.getElementById('mysearchestable');
    if(table.rows.length > 0) {
        table.getElementsByTagName('tr')[table.rows.length-1].style.borderBottom = '0';
    }
    if(table.rows.length == 0) {
        showstuff('nosearches');
    }
    url = url + 'javascript/callbacks/deletesearches.php';
    var myAjax = new Ajax.Request(url, {method: 'get', parameters: 'id=' + id });
}
function savesearch(query, url, savetext) {
    url = url + 'javascript/callbacks/savesearches.php?' + query + '&search_name=' + document.getElementById('save_search').value;
    if(document.getElementById('save_search').value != '') {
        setTimeout('reverttext("' + document.getElementById('lblsaveas').firstChild.nodeValue + '")', 2000)
        document.getElementById('lblsaveas').firstChild.nodeValue = savetext;
    }
    var myAjax = new Ajax.Request(url, {method: 'get'});
}
function reverttext(text) {
    document.getElementById('lblsaveas').firstChild.nodeValue = text;
}
function activelink(id) {
    if(document.getElementById(id).className == 'active') {
        document.getElementById(id).className = 'inactive';
    } else {
        document.getElementById(id).className = 'active';
    }
}
var useAltForKeyboardNavigation = false;  // Set this to true if you don't want to kill
                                         // Firefox's find as you type 

function getVoters(id, baseurl, field)
{
    var url = baseurl + 'javascript/callbacks/getvoters.php?id=' + id;
    var myAjax = new Ajax.Updater(field, url, { method: 'get'});
}
function checkname(value){
    new Ajax.Request('javascript/callbacks/searchnames.php?name='+value, {onSuccess: function(t){ allow(t.responseText); } });
}
function allow(booler){
    if(booler.indexOf("false") > -1) {
        $('username').style.color ="red";
        $('buSubmit').style.visibility = "hidden";
        $('errormessage').innerHTML = booler.substring(6,booler.length);
    }
    else {
        $('username').style.color ="green";
        $('buSubmit').style.visibility = "show";
        $('errormessage').innerHTML = "";
    }  
}
function getHistory(id, baseurl, field)
{
    var url = baseurl + 'javascript/callbacks/gethistory.php?id=' + id;
    var myAjax = new Ajax.Updater(field, url, { method: 'get'});
}

function getHistoryDetail(id, baseurl, field, details)
{
    var url = baseurl + 'javascript/callbacks/gethistory.php?id=' + id + '&details=' + details;
    var myAjax = new Ajax.Updater(field, url, { method: 'get'});
}
