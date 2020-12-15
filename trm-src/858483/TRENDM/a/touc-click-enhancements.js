 var isMouseDown = false;
    var mouseJoint;
    var mouse = {
        x: 0,
        y: 0
    };
    var gravity = {
        x: 0,
        y: -1
    };

    var panel1 = document.getElementById('#nav2');
    var panel2 = document.getElementById('#site-content');

    panel1.onmousedown = onDocumentMouseDown;
    panel1.onmouseup = onDocumentMouseUp;
    panel1.onmousemove = onDocumentMouseMove;
    panel1.addEventListener('touchstart', onDocumentTouchStart, false);
    panel1.addEventListener('touchmove', onDocumentTouchMove, false);
    panel1.addEventListener('touchend', onDocumentTouchEnd, false);

    panel2.onmousedown = onDocumentMouseDown;
    panel2.onmouseup = onDocumentMouseUp;
    panel2.onmousemove = onDocumentMouseMove;
    panel2.addEventListener('touchstart', onDocumentTouchStart, false);
    panel2.addEventListener('touchmove', onDocumentTouchMove, false);
    panel2.addEventListener('touchend', onDocumentTouchEnd, false);



    function onDocumentMouseDown(event) {
        event.preventDefault();
        panel1.style.background = "#2E442E";
        panel2.style.background = "#5D855C";
    }

    function onDocumentMouseUp(event) {
        event.preventDefault();
        this.style.background = "#ff0000";
        console.log(this.id);
    }

    function onDocumentMouseMove(event) {
        
    }

    function onDocumentTouchStart(event) {
        event.preventDefault();
        panel1.style.background = "#2E442E";
        panel2.style.background = "#5D855C";

    }

    function onDocumentTouchMove(event) {
        onDocumentTouchMove.x = event.changedTouches[event.changedTouches.length - 1].clientX;
        onDocumentTouchMove.y = event.changedTouches[event.changedTouches.length - 1].clientY;
    }

    function onDocumentTouchEnd(event) {
        event.preventDefault();
        var elem = document.getElementById((document.elementFromPoint(onDocumentTouchMove.x, onDocumentTouchMove.y)).id);
        elem.style.background = "#ff0000";
    }












function startup() {
  var el =document.body;
  el.addEventListener("touchstart", handleStart, false);
  el.addEventListener("touchend", handleEnd, false);
  el.addEventListener("touchcancel", handleCancel, false);
  el.addEventListener("touchleave", handleEnd, false);
  el.addEventListener("touchmove", handleMove, false);
  log("initialized.");
}
var ongoingTouches = new Array;
function handleStart(evt) {
    
 
//  log("touchstart.");
  var el = document.getElementsByTagName("canvas")[0];
  var ctx = el.getContext("2d");
  var touches = evt.changedTouches;
var offset = findPos(el);  
    
   
  for (var i = 0; i < touches.length; i++) {
      if(touches[i].clientX-offset.x >0 && touches[i].clientX-offset.x < parseFloat(el.width) && touches[i].clientY-offset.y >0 && touches[i].clientY-offset.y < parseFloat(el.height)){
            evt.preventDefault();
    log("touchstart:" + i + "...");
    ongoingTouches.push(copyTouch(touches[i]));
    var color = colorForTouch(touches[i]);
    ctx.beginPath();
    ctx.arc(touches[i].clientX-offset.x, touches[i].clientY-offset.y, 4, 0, 2 * Math.PI, false); // a circle at the start
    ctx.fillStyle = color;
    ctx.fill();
    log("touchstart:" + i + ".");
  }
  }
}
function handleMove(evt) {

  var el = document.getElementsByTagName("canvas")[0];
  var ctx = el.getContext("2d");
  var touches = evt.changedTouches;
  var offset = findPos(el);

  for (var i = 0; i < touches.length; i++) {
        if(touches[i].clientX-offset.x >0 && touches[i].clientX-offset.x < parseFloat(el.width) && touches[i].clientY-offset.y >0 && touches[i].clientY-offset.y < parseFloat(el.height)){
              evt.preventDefault();
      var color = colorForTouch(touches[i]);
    var idx = ongoingTouchIndexById(touches[i].identifier);
    
    if (idx >= 0) {
      log("continuing touch " + idx);
      ctx.beginPath();
      log("ctx.moveTo(" + ongoingTouches[idx].clientX + ", " + ongoingTouches[idx].clientY + ");");
      ctx.moveTo(ongoingTouches[idx].clientX-offset.x, ongoingTouches[idx].clientY-offset.y);
      log("ctx.lineTo(" + touches[i].clientX + ", " + touches[i].clientY + ");");
      ctx.lineTo(touches[i].clientX-offset.x, touches[i].clientY-offset.y);
      ctx.lineWidth = 4;
      ctx.strokeStyle = color;
      ctx.stroke();
      
      ongoingTouches.splice(idx, 1, copyTouch(touches[i])); // swap in the new touch record
      log(".");
    } else {
      log("can't figure out which touch to continue");
    }
  }
        }
}
function handleEnd(evt) {

//  log("touchend/touchleave.");
  var el = document.getElementsByTagName("canvas")[0];
  var ctx = el.getContext("2d");
  var touches = evt.changedTouches;
  var offset = findPos(el);
        
  for (var i = 0; i < touches.length; i++) {
              if(touches[i].clientX-offset.x >0 && touches[i].clientX-offset.x < parseFloat(el.width) && touches[i].clientY-offset.y >0 && touches[i].clientY-offset.y < parseFloat(el.height)){
                    evt.preventDefault();
    var color = colorForTouch(touches[i]);
    var idx = ongoingTouchIndexById(touches[i].identifier);
        
    if (idx >= 0) {
      ctx.lineWidth = 4;
      ctx.fillStyle = color;
      ctx.beginPath();
      ctx.moveTo(ongoingTouches[idx].clientX-offset.x, ongoingTouches[idx].clientY-offset.y);
      ctx.lineTo(touches[i].clientX-offset.x, touches[i].clientY-offset.y);
      ctx.fillRect(touches[i].clientX - 4-offset.x, touches[i].clientY - 4-offset.y, 8, 8); // and a square at the end
      ongoingTouches.splice(i, 1); // remove it; we're done
    } else {
      log("can't figure out which touch to end");
    }
  }
        }
}
function handleCancel(evt) {
  evt.preventDefault();
  log("touchcancel.");
  var touches = evt.changedTouches;
  
  for (var i = 0; i < touches.length; i++) {
    ongoingTouches.splice(i, 1); // remove it; we're done
  }
}
function colorForTouch(touch) {
  var r = touch.identifier % 16;
  var g = Math.floor(touch.identifier / 3) % 16;
  var b = Math.floor(touch.identifier / 7) % 16;
  r = r.toString(16); // make it a hex digit
  g = g.toString(16); // make it a hex digit
  b = b.toString(16); // make it a hex digit
  var color = "#" + r + g + b;
  log("color for touch with identifier " + touch.identifier + " = " + color);
  return color;
}
function copyTouch(touch) {
  return {identifier: touch.identifier,clientX: touch.clientX,clientY: touch.clientY};
}
function ongoingTouchIndexById(idToFind) {
  for (var i = 0; i < ongoingTouches.length; i++) {
    var id = ongoingTouches[i].identifier;
    
    if (id == idToFind) {
      return i;
    }
  }
  return -1; // not found
}
function log(msg) {
  var p = document.getElementById('log');
  p.innerHTML = msg + "\n" + p.innerHTML;
}
 
function findPos (obj) {
    var curleft = 0,
        curtop = 0;

    if (obj.offsetParent) {
        do {
            curleft += obj.offsetLeft;
            curtop += obj.offsetTop;
        } while (obj = obj.offsetParent);

        return { x: curleft-document.body.scrollLeft, y: curtop-document.body.scrollTop };
    }
}


    onclick="startup()