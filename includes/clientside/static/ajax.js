/*
 * AJAX applets
 */
 
function ajaxGet(uri, f) {
  if (window.XMLHttpRequest) {
    ajax = new XMLHttpRequest();
  } else {
    if (window.ActiveXObject) {           
      ajax = new ActiveXObject("Microsoft.XMLHTTP");
    } else {
      alert('Enano client-side runtime error: No AJAX support, unable to continue');
      return;
    }
  }
  ajax.onreadystatechange = f;
  ajax.open('GET', uri, true);
  ajax.setRequestHeader( "If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT" );
  ajax.send(null);
}

function ajaxPost(uri, parms, f) {
  if (window.XMLHttpRequest) {
    ajax = new XMLHttpRequest();
  } else {
    if (window.ActiveXObject) {           
      ajax = new ActiveXObject("Microsoft.XMLHTTP");
    } else {
      alert('Enano client-side runtime error: No AJAX support, unable to continue');
      return;
    }
  }
  ajax.onreadystatechange = f;
  ajax.open('POST', uri, true);
  ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  // Setting Content-length in Safari triggers a warning
  if ( !is_Safari )
  {
    ajax.setRequestHeader("Content-length", parms.length);
  }
  ajax.setRequestHeader("Connection", "close");
  ajax.send(parms);
}

function ajaxEscape(text)
{
  text = escape(text);
  text = text.replace(/\+/g, '%2B', text);
  return text;
}

// Page editor

function ajaxEditor()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=getsource', function() {
      if(ajax.readyState == 4) {
        unsetAjaxLoading();
        if(edit_open) {
          c=confirm('Do you really want to revert your changes?');
          if(!c) return;
        }
        edit_open = true;
        selectButtonMajor('article');
        selectButtonMinor('edit');
        if(in_array('ajaxEditArea', grippied_textareas))
        {
          // Allow the textarea grippifier to re-create the resizer control on the textarea
          grippied_textareas.pop(in_array('ajaxEditArea', grippied_textareas));
        }
        disableUnload('If you do, any changes that you have made to this page will be lost.');
        var switcher = ( readCookie('enano_editor_mode') == 'tinymce' ) ?
                        '<a href="#" onclick="setEditorText(); return false;">wikitext editor</a>  |  graphical editor' :
                        'wikitext editor  |  <a href="#" onclick="setEditorMCE(); return false;">graphical editor</a>' ;
        document.getElementById('ajaxEditContainer').innerHTML = '\
        <div id="mdgPreviewContainer"></div> \
        <span id="switcher">' + switcher + '</span><br />\
        <form name="mdgAjaxEditor" method="get" action="#" onsubmit="ajaxSavePage(); return false;">\
        <textarea id="ajaxEditArea" rows="20" cols="60" style="display: block; margin: 1em 0 1em 1em; width: 96.5%;">'+ajax.responseText+'</textarea><br />\
          Edit summary: <input id="ajaxEditSummary" size="40" /><br />\
          <input id="ajaxEditMinor" name="minor" type="checkbox" /> <label for="ajaxEditMinor">This is a minor edit</label><br />\
          <a href="#" onclick="void(ajaxSavePage()); return false;">save changes</a>  |  <a href="#" onclick="void(ajaxShowPreview()); return false;">preview changes</a>  |  <a href="#" onclick="void(ajaxEditor()); return false;">revert changes</a>  |  <a href="#" onclick="void(ajaxDiscard()); return false;">discard changes</a>  |  <a href="#" onclick="ajaxWikiEditHelp(); return false;">formatting help</a>\
          <br />\
          '+editNotice+'\
        </form>';
        // initTextareas();
        if(readCookie('enano_editor_mode') == 'tinymce')
        {
          $('ajaxEditArea').switchToMCE();
        }
      }
  });
}

function setEditorMCE()
{
  $('ajaxEditArea').switchToMCE();
  createCookie('enano_editor_mode', 'tinymce', 365);
  $('switcher').object.innerHTML = '<a href="#" onclick="setEditorText(); return false;">wikitext editor</a>  |  graphical editor';
}

function setEditorText()
{
  $('ajaxEditArea').destroyMCE();
  createCookie('enano_editor_mode', 'text', 365);
  $('switcher').object.innerHTML = 'wikitext editor  |  <a href="#" onclick="setEditorMCE(); return false;">graphical editor</a>';
}

function ajaxViewSource()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=getsource', function() {
      if(ajax.readyState == 4) {
        unsetAjaxLoading();
        if(edit_open) {
          c=confirm('Do you really want to revert your changes?');
          if(!c) return;
        }
        edit_open = true;
        selectButtonMajor('article');
        selectButtonMinor('edit');
        if(in_array('ajaxEditArea', grippied_textareas))
        {
          // Allow the textarea grippifier to re-create the resizer control on the textarea
          grippied_textareas.pop(in_array('ajaxEditArea', grippied_textareas));
        }
        document.getElementById('ajaxEditContainer').innerHTML = '\
          <form method="get" action="#" onsubmit="ajaxSavePage(); return false;">\
            <textarea readonly="readonly" id="ajaxEditArea" rows="20" cols="60" style="display: block; margin: 1em 0 1em 1em; width: 96.5%;">'+ajax.responseText+'</textarea><br />\
            <a href="#" onclick="void(ajaxReset()); return false;">close viewer</a>\
          </form>';
        initTextareas();
      }
  });
}

function ajaxShowPreview()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  goBusy('Loading preview...');
  var text = ajaxEscape($('ajaxEditArea').getContent());
  if(document.mdgAjaxEditor.minor.checked) minor='&minor';
  else minor='';
  ajaxPost(stdAjaxPrefix+'&_mode=preview', 'summary='+document.getElementById('ajaxEditSummary').value+minor+'&text='+text, function() {
    if(ajax.readyState == 4) {
      unBusy();
      edit_open = false;
      document.getElementById('mdgPreviewContainer').innerHTML = ajax.responseText;
    }
  });
}

function ajaxSavePage()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  goBusy('Saving page...');
  var text = ajaxEscape($('ajaxEditArea').getContent());
  if(document.mdgAjaxEditor.minor.checked) minor='&minor';
  else minor='';
  ajaxPost(stdAjaxPrefix+'&_mode=savepage', 'summary='+document.getElementById('ajaxEditSummary').value+minor+'&text='+text, function() {
    if(ajax.readyState == 4) {
      unBusy();
      edit_open = false;
      document.getElementById('ajaxEditContainer').innerHTML = ajax.responseText;
      enableUnload();
      unselectAllButtonsMinor();
    }
  });
}

function ajaxDiscard()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  c = confirm('Do you really want to discard your changes?');
  if(!c) return;
  ajaxReset();
}

function ajaxReset()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  enableUnload();
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=getpage&noheaders', function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      edit_open = false;
      document.getElementById('ajaxEditContainer').innerHTML = ajax.responseText;
      selectButtonMajor('article');
      unselectAllButtonsMinor();
    }
  });
}

// Miscellaneous AJAX applets

function ajaxProtect(l) {
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  if(shift) {
    r = 'NO_REASON';
  } else {
    r = prompt('Reason for (un)protecting:');
    if(!r || r=='') return;
  }
  setAjaxLoading();
  document.getElementById('protbtn_0').style.textDecoration = 'none';
  document.getElementById('protbtn_1').style.textDecoration = 'none';
  document.getElementById('protbtn_2').style.textDecoration = 'none';
  document.getElementById('protbtn_'+l).style.textDecoration = 'underline';
  ajaxPost(stdAjaxPrefix+'&_mode=protect', 'reason='+escape(r)+'&level='+l, function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      if(ajax.responseText != 'good')
        alert(ajax.responseText);
    }
  });
}

function ajaxRename()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  r = prompt('What title should this page be renamed to?\nNote: This does not and will never change the URL of this page, that must be done from the admin panel.');
  if(!r || r=='') return;
  setAjaxLoading();
  ajaxPost(stdAjaxPrefix+'&_mode=rename', 'newtitle='+escape(r), function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      alert(ajax.responseText);
    }
  });
}

function ajaxMakePage()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  setAjaxLoading();
  ajaxPost(ENANO_SPECIAL_CREATEPAGE, ENANO_CREATEPAGE_PARAMS, function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      window.location.reload();
    }
  });
}

function ajaxDeletePage()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  var reason = prompt('Please enter your reason for deleting this page.');
  if ( !reason || reason == '' )
  {
    return false;
  }
  c = confirm('You are about to REVERSIBLY delete this page. Do you REALLY want to do this?\n\n(Comments and categorization data, as well as any attached files, will be permanently lost)');
  if(!c)
  {
    return;
  }
  setAjaxLoading();
  ajaxPost(stdAjaxPrefix+'&_mode=deletepage', 'reason=' + escape(reason), function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      alert(ajax.responseText);
      window.location.reload();                                                                           
    }
  });
}

function ajaxDelVote()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  c = confirm('Are you sure that you want to vote that this page be deleted?');
  if(!c) return;
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=delvote', function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      alert(ajax.responseText);
    }
  });
}

function ajaxResetDelVotes()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  c = confirm('This will reset the number of votes against this page to zero. Do you really want to do this?');
  if(!c) return;
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=resetdelvotes', function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      alert(ajax.responseText);
      item = document.getElementById('mdgDeleteVoteNoticeBox');
      if(item)
      {
        opacity('mdgDeleteVoteNoticeBox', 100, 0, 1000);
        setTimeout("document.getElementById('mdgDeleteVoteNoticeBox').style.display = 'none';", 1000);
      }
    }
  });
}

function ajaxSetWikiMode(val) {
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  setAjaxLoading();
  document.getElementById('wikibtn_0').style.textDecoration = 'none';
  document.getElementById('wikibtn_1').style.textDecoration = 'none';
  document.getElementById('wikibtn_2').style.textDecoration = 'none';
  document.getElementById('wikibtn_'+val).style.textDecoration = 'underline';
  ajaxGet(stdAjaxPrefix+'&_mode=setwikimode&mode='+val, function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      if(ajax.responseText!='GOOD')
      {
        alert(ajax.responseText);
      }
    }
  });
}

// Editing/saving category information
// This was not easy to write, I hope enjoy it, and dang I swear I'm gonna
// find someone to work on just the Javascript part of Enano...

function ajaxCatEdit()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=catedit', function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      edit_open = false;
      eval(ajax.responseText);
    }
  });
}

function ajaxCatSave()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  if(!catlist)
  {
    alert('Var catlist has no properties');
    return;
  }
  query='';
  for(i=0;i<catlist.length;i++)
  {
    l = 'if(document.forms.mdgCatForm.mdgCat_'+catlist[i]+'.checked) s = true; else s = false;';
    eval(l);
    if(s) query = query + '&' + catlist[i] + '=true';
  }
  setAjaxLoading();
  query = query.substring(1, query.length);
  ajaxPost(stdAjaxPrefix+'&_mode=catsave', query, function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      edit_open = false;
      if(ajax.responseText != 'GOOD') alert(ajax.responseText);
      ajaxReset();
    }
  });
}

// History stuff

function ajaxHistory()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=histlist', function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      edit_open = false;
      selectButtonMajor('article');
      selectButtonMinor('history');
      document.getElementById('ajaxEditContainer').innerHTML = ajax.responseText;
      buildDiffList();
    }
  });
}

function ajaxHistView(oldid, tit) {
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  if(!tit) tit=title;
  setAjaxLoading();
  ajaxGet(append_sid(scriptPath+'/ajax.php?title='+tit+'&_mode=getpage&oldid='+oldid), function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      edit_open = false;
      document.getElementById('ajaxEditContainer').innerHTML = ajax.responseText;
    }
  });
}

function ajaxRollback(id) {
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=rollback&id='+id, function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      alert(ajax.responseText);
    }
  });
}

function ajaxClearLogs()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  c = confirm('You are about to DESTROY all log entries for this page. As opposed to (example) deleting this page, this action is completely IRREVERSIBLE and should not be used except in dire circumstances. Do you REALLY want to do this?');
  if(!c) return;
  c = confirm('You\'re ABSOLUTELY sure???');
  if(!c) return;
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=flushlogs', function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      alert(ajax.responseText);
      window.location.reload();
    }
  });
}

var timelist;

function buildDiffList()
{
  arrDiff1Buttons = getElementsByClassName(document, 'input', 'clsDiff1Radio');
  arrDiff2Buttons = getElementsByClassName(document, 'input', 'clsDiff2Radio');
  var len = arrDiff1Buttons.length;
  if ( len < 1 )
    return false;
  timelist = new Array();
  for ( var i = 0; i < len; i++ )
  {
    timelist.push( arrDiff2Buttons[i].id.substr(6) );
  }
  timelist.push( arrDiff1Buttons[len-1].id.substr(6) );
  delete(timelist.toJSONString);
  for ( var i = 1; i < timelist.length-1; i++ )
  {
    if ( i >= timelist.length ) break;
    arrDiff2Buttons[i].style.display = 'none';
  }
}

function selectDiff1Button(obj)
{
  var this_time = obj.id.substr(6);
  var index = parseInt(in_array(this_time, timelist));
  for ( var i = 0; i < timelist.length - 1; i++ )
  {
    if ( i < timelist.length - 1 )
    {
      var state = ( i < index ) ? 'inline' : 'none';
      var id = 'diff2_' + timelist[i];
      document.getElementById(id).style.display = state;
      
      // alert("Debug:\nIndex: "+index+"\nState: "+state+"\ni: "+i);
    }
  }
}

function selectDiff2Button(obj)
{
  var this_time = obj.id.substr(6);
  var index = parseInt(in_array(this_time, timelist));
  for ( var i = 1; i < timelist.length; i++ )
  {
    if ( i < timelist.length - 1 )
    {
      var state = ( i > index ) ? 'inline' : 'none';
      var id = 'diff1_' + timelist[i];
      document.getElementById(id).style.display = state;
      
      // alert("Debug:\nIndex: "+index+"\nState: "+state+"\ni: "+i);
    }
  }
}

function ajaxHistDiff()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  var id1=false;
  var id2=false;
  for ( i = 0; i < arrDiff1Buttons.length; i++ )
  {
    k = i + '';
    kpp = i + 1;
    kpp = kpp + '';
    if(arrDiff1Buttons[k].checked) id1 = arrDiff1Buttons[k].id.substr(6);
    if(arrDiff2Buttons[k].checked) id2 = arrDiff2Buttons[k].id.substr(6);
  }
  if(!id1 || !id2) { alert('BUG: Couldn\'t get checked radiobutton state'); return; }
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=pagediff&diff1='+id1+'&diff2='+id2, function()
    {
      if(ajax.readyState==4)
      {
        unsetAjaxLoading();
        document.getElementById('ajaxEditContainer').innerHTML = ajax.responseText;
      }
    });
}

// Change the user's preferred style/theme

function ajaxChangeStyle()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  var inner_html = '';
  inner_html += '<p><label>Theme: ';
  inner_html += '  <select id="chtheme_sel_theme" onchange="ajaxGetStyles(this.value);">';
  inner_html += '    <option value="_blank" selected="selected">[Select]</option>';
  inner_html +=      ENANO_THEME_LIST;
  inner_html += '  </select>';
  inner_html += '</label></p>';
  var chtheme_mb = new messagebox(MB_OKCANCEL|MB_ICONQUESTION, 'Change your theme', inner_html);
  chtheme_mb.onbeforeclick['OK'] = ajaxChangeStyleComplete;
}

function ajaxGetStyles(id)
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  var thediv = document.getElementById('chtheme_sel_style_parent');
  if ( thediv )
  {
    thediv.parentNode.removeChild(thediv);
  }
  if ( id == '_blank' )
  {
    return null;
  }
  ajaxGet(stdAjaxPrefix + '&_mode=getstyles&id=' + id, function() {
      if ( ajax.readyState == 4 )
      {
        // IE doesn't like substr() on ajax.responseText
        var response = String(ajax.responseText + ' ');
        response = response.substr(0, response.length - 1);
        if ( response.substr(0,1) != '[' )
        {
          alert('Invalid or unexpected JSON response from server:\n' + response);
          return null;
        }
        
        // Build a selector and matching label
        var data = parseJSON(response);
        var options = new Array();
        for( var i in data )
        {
          var item = data[i];
          var title = themeid_to_title(item);
          var option = document.createElement('option');
          option.value = item;
          option.appendChild(document.createTextNode(title));
          options.push(option);
        }
        var p_parent = document.createElement('p');
        var label  = document.createElement('label');
        p_parent.id = 'chtheme_sel_style_parent';
        label.appendChild(document.createTextNode('Style: '));
        var select = document.createElement('select');
        select.id = 'chtheme_sel_style';
        for ( var i in options )
        {
          select.appendChild(options[i]);
        }
        label.appendChild(select);
        p_parent.appendChild(label);
        
        // Stick it onto the messagebox
        var div = document.getElementById('messageBox');
        var kid = div.firstChild.nextSibling;
        
        kid.appendChild(p_parent);
        
      }
    });
}

function ajaxChangeStyleComplete()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  var theme = $('chtheme_sel_theme');
  var style = $('chtheme_sel_style');
  if ( !theme.object || !style.object )
  {
    alert('Please select a theme from the list.');
    return true;
  }
  var theme_id = theme.object.value;
  var style_id = style.object.value;
  
  if ( typeof(theme_id) != 'string' || typeof(style_id) != 'string' )
  {
    alert('Couldn\'t get theme or style ID');
    return true;
  }
  
  if ( theme_id.length < 1 || style_id.length < 1 )
  {
    alert('Theme or style ID is zero length');
    return true;
  }
  
  ajaxPost(stdAjaxPrefix + '&_mode=change_theme', 'theme_id=' + escape(theme_id) + '&style_id=' + escape(style_id), function()
    {
      if ( ajax.readyState == 4 )
      {
        if ( ajax.responseText == 'GOOD' )
        {
          var c = confirm('Your theme preference has been changed.\nWould you like to reload the page now to see the changes?');
          if ( c )
            window.location.reload();
        }
        else
        {
          alert('Error occurred during attempt to change theme:\n' + ajax.responseText);
        }
      }
    });
  
  return false;
  
}

function themeid_to_title(id)
{
  if ( typeof(id) != 'string' )
    return false;
  id = id.substr(0, 1).toUpperCase() + id.substr(1);
  id = id.replace(/_/g, ' ');
  id = id.replace(/-/g, ' ');
  return id;
}

/*
function ajaxChangeStyle()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  var win = document.getElementById("cn2");
  win.innerHTML = ' \
    <form action="'+ENANO_SPECIAL_CHANGESTYLE+'" onsubmit="jws.closeWin(\'root2\');" method="post" style="text-align: center"> \
    <h3>Select a theme...</h3>\
    <select id="mdgThemeID" name="theme" onchange="ajaxGetStyles(this.value);"> \
    '+ENANO_THEME_LIST+' \
    </select> \
    <div id="styleSelector"></div>\
    <br /><br />\
    <input type="hidden" name="return_to" value="'+title+'" />\
    <input id="styleSubmitter" type="submit" style="display: none; font-weight: bold" value="Change theme" /> \
    <input type="button" value="Cancel" onclick="jws.closeWin(\'root2\');" /> \
    </form> \
  ';
  ajaxGetStyles(ENANO_CURRENT_THEME);
  jws.openWin('root2', 340, 300);
}

function ajaxGetStyles(id) {
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=getstyles&id='+id, function() {
    if(ajax.readyState == 4) {
      unsetAjaxLoading();
      eval(ajax.responseText);
      html = '<h3>And a style...</h3><select id="mdgStyleID" name="style">';
      for(i=0;i<list.length;i++) {
        lname = list[i].substr(0, 1).toUpperCase() + list[i].substr(1, list[i].length);
        html = html + '<option value="'+list[i]+'">'+lname+'</option>';
      }
      html = html + '</select>';
      document.getElementById('styleSelector').innerHTML = html;
      document.getElementById('styleSubmitter').style.display = 'inline'; 
    }
  });
}
*/

function ajaxSwapCSS()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  setAjaxLoading();
  if(_css) {
    document.getElementById('mdgCss').href = main_css;
    _css = false;
  } else {
    document.getElementById('mdgCss').href = print_css;
    _css = true;
  }
  unsetAjaxLoading();
  menuOff();
}

function ajaxSetPassword()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  pass = hex_sha1(document.getElementById('mdgPassSetField').value);
  setAjaxLoading();
  ajaxPost(stdAjaxPrefix+'&_mode=setpass', 'password='+pass, function()
    {
      unsetAjaxLoading();
      if(ajax.readyState==4)
      {
        alert(ajax.responseText);
      }
    }
  );
}

function ajaxWikiEditHelp()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  jws.openWin('root3', 640, 480);
  setAjaxLoading();
  ajaxGet(stdAjaxPrefix+'&_mode=wikihelp', function() {
      if(ajax.readyState==4)
      {
        unsetAjaxLoading();
        document.getElementById('cn3').innerHTML = ajax.responseText;
      }
    });
}

function ajaxStartLogin()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  ajaxPromptAdminAuth(function(k) {
      window.location.reload();
    }, USER_LEVEL_MEMBER);
}

function ajaxStartAdminLogin()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  if ( auth_level < USER_LEVEL_ADMIN )
  {
    ajaxPromptAdminAuth(function(k) {
      ENANO_SID = k;
      auth_level = USER_LEVEL_ADMIN;
      var loc = makeUrlNS('Special', 'Administration');
      if ( (ENANO_SID + ' ').length > 1 )
        window.location = loc;
    }, USER_LEVEL_ADMIN);
    return false;
  }
  var loc = makeUrlNS('Special', 'Administration');
  window.location = loc;
}

function ajaxAdminPage()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  if ( auth_level < USER_LEVEL_ADMIN )
  {
    ajaxPromptAdminAuth(function(k) {
      ENANO_SID = k;
      auth_level = USER_LEVEL_ADMIN;
      var loc = String(window.location + '');
      window.location = append_sid(loc);
      var loc = makeUrlNS('Special', 'Administration', 'module=' + namespace_list['Admin'] + 'PageManager&source=ajax&page_id=' + ajaxEscape(title));
      if ( (ENANO_SID + ' ').length > 1 )
        window.location = loc;
    }, 9);
    return false;
  }
  var loc = makeUrlNS('Special', 'Administration', 'module=' + namespace_list['Admin'] + 'PageManager&source=ajax&page_id=' + ajaxEscape(title));
  window.location = loc;
}

function ajaxDisableEmbeddedPHP()
{
  // IE <6 pseudo-compatibility
  if ( KILL_SWITCH )
    return true;
  if ( !confirm('Are you really sure you want to do this? Some pages might not function if this emergency-only feature is activated.') )
    return false;
  var $killdiv = $dynano('php_killer');
  if ( !$killdiv.object )
  {
    alert('Can\'t get kill div object');
    return false;
  }
  $killdiv.object.innerHTML = '<img alt="Loading..." src="' + scriptPath + '/images/loading-big.gif" /><br />Making request...';
  var url = makeUrlNS('Admin', 'Home', 'src=ajax');
  ajaxPost(url, 'act=kill_php', function() {
      if ( ajax.readyState == 4 )
      {
        if ( ajax.responseText == '1' )
        {
          var $killdiv = $dynano('php_killer');
          //$killdiv.object.innerHTML = '<img alt="Success" src="' + scriptPath + '/images/error.png" /><br />Embedded PHP in pages has been disabled.';
          $killdiv.object.parentNode.removeChild($killdiv.object);
          var newdiv = document.createElement('div');
          // newdiv.style = $killdiv.object.style;
          newdiv.className = $killdiv.object.className;
          newdiv.innerHTML = '<img alt="Success" src="' + scriptPath + '/images/error.png" /><br />Embedded PHP in pages has been disabled.';
          $killdiv.object.parentNode.appendChild(newdiv);
          $killdiv.object.parentNode.removeChild($killdiv.object);
        }
        else
        {
          var $killdiv = $dynano('php_killer');
          $killdiv.object.innerHTML = ajax.responseText;
        }
      }
    });
}

