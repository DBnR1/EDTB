// -- This is a modified version of the original script 
// -- from CodeLifter.com, in order to fit in SPGM

// SlideShow with Captions and Cross-Fade
// (C) 2002 www.CodeLifter.com
// http://www.codelifter.com
// Free for all users, but leave in this header.

// ==============================
// Set the following variables...
// ==============================

// Set the slideshow speed (in milliseconds)
var SlideShowSpeed = 3000;

// Set the duration of crossfade (in seconds)
var CrossFadeDuration = 3;


var tss;
var jss = 1;
var iss = 1;

var preLoad = new Array();
var arrCaptions = new Array();
var arrWidth = new Array();
var arrHeight = new Array();

function runSlideShow() {

  if ( document.getElementById('picture') ) {
    if (document.all && !window.opera){
      document.getElementById('picture').style.filter="blendTrans(duration=2)";
      document.getElementById('picture').style.filter="blendTrans(duration=CrossFadeDuration)";
      document.getElementById('picture').filters.blendTrans.Apply();
    }
    document.getElementById('picture').setAttribute('width', arrWidth[jss]);
    document.getElementById('picture').setAttribute('height', arrHeight[jss]);
    document.getElementById('picture').src = preLoad[jss].src;
    if (document.all && !window.opera){
      document.getElementById('picture').filters.blendTrans.Play();
    }
    if ( document.getElementById('picture-caption') ) {
      document.getElementById('picture-caption').innerHTML = arrCaptions[jss];
    }
  }

  jss = jss + 1;
  if (jss > (iss - 1)) jss=1;
  tss = setTimeout('runSlideShow()', SlideShowSpeed);
}

function addPicture(pictureURL, caption, width, height) {
  preLoad[iss] = new Image();
  preLoad[iss].src = pictureURL;
  arrCaptions[iss] = caption;
  arrWidth[iss] = width;
  arrHeight[iss] = height;
  iss = iss + 1;
}

function popupPicture(pictureURL, width, height, justPicture) {
  if (justPicture) {
    var frame = document.open('', '', 'width='+width+',height='+height+',scrollbars=0,location=0,menubar=0,resizable=1');
    frame.document.write(
      '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '
      +'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\n'
      +'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">\n'
      +'  <head>\n'
      +'    <title>SPGM: '+pictureURL+'</title>'
      +'    <style type="text/css">body {margin: 0px}</style>\n'
      +'    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />\n'
      +'  </head>\n'
      +'  <body>\n'
      +'    <div style="margin: 0px;">\n'
      +'      <img src="'+pictureURL+'" alt="'+pictureURL+'" />\n'
      +'    </div>\n'
      +'  </body>\n'
      +'</html>'
    );
    frame.document.close();
    return true;
  } else {
    document.open(pictureURL, '', 'width='+width+',height='+height+',scrollbars=1,location=0,menubar=0,resizable=1');
  }
}

