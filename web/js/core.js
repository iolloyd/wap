/*
(function(){
	var Ajax = {
	ajaxob: function(){
		return (window.XMLHttpRequest) 
            ? new XMLHttpRequest() 
            : new ActiveXObject('Microsoft.XMLHTTP'); 
	},
	call: function(url, f, async){
        var async = async || true;
		var ob = Ajax.ajaxob();
		ob.onreadystatechange = function(){
            if ((ob.readyState == 4) && 
               (ob.status == 200)
            ){
				return f(ob.responseText);
			}
		};
		ob.open("get", url, async);
		ob.setRequestHeader( 'if-modified-since','wed, 16 jul 1970 00:00:00 gmt' );
		var back = ob.send(null);
	}
};

Array.prototype.filter = function(f){
	var f = (typeof(f) == 'function') ? f : f.fun();
	var out = [];
	for (var i=0, len=this.length; val=this[i]; i++) 
		if (f(val)) out.push(val);
	return out;
}

Array.prototype.head = function(){
    if (!this.length) return false;
    return this[0];
}

Array.prototype.iter = function(f){
	var f = (typeof(f) == 'function') ? f : f.fun();
	for (var i=0, len=this.length; val=this[i], i < len; i++) 
		f(val);
}

Array.prototype.map = function(f){
	var f = (typeof(f) == 'function') ? f : f.fun();
	var out = [];
	for (var i=0, len=this.length; val=this[i], i < len; i++) {
		out.push(f(val));
	}
	return out;
}

Array.prototype.reduce = function(f){
	var f = (typeof(f) == 'function') ? f : f.fun();
	var out = [];
	for (var i=0, len=this.length; v=this[i], i<len; i++){
		out = f(v, out);
	}
	return out;
}

Array.prototype.tail = function(){
    if (this.length < 1) {
		return false;
	}
    return this.slice(1);
}

var Css = {
	'removeClassName': function(e,t) {
		if (typeof e == "string") e = $(e);
		var ec = ' ' + e.className.replace(/^s*|s*$/g,'') + ' ';
		var nc = ec;
		t = t.replace(/^s*|s*$/g,'');
		if (ec.indexOf(' '+t+' ') != -1) {
			nc = ec.replace(' ' + t.replace(/^s*|s*$/g,'') + ' ',' ');
		}
		e.className = nc.replace(/^s*|s*$/g,''); 
		return true;
	}


}

Debug = {
	showArgs: function(){},

    debug: function(message,clear){
        var c = clear || false
        var m = message || '';
        if (!$('debugger')){
            var dw=document.createElement('div');
            dw.setAttribute('id','debugger');
            dw.style.position='absolute';
            dw.style.height='140px';
            dw.style.backgroundColor='white';
            dw.style.overflow='scroll';
            var fc = document.body.firstChild;
            fc.parentNode.insertBefore(dw,fc);
            dw.style.position='absolute';
        }
        var db = $('debugger');
        (clear) ? db.innerHTML = m : db.innerHTML += "<br>--> " + m;
    }
}

DataEvent = {
    tx: function(target,dtype,d){
        if (typeof target == "string") 
            target = document.getElementById(target);

        if (document.createEvent){
            var e = document.createEvent("Events");
            e.initEvent("dataready", true, false);

        }else if (document.createEventObject){
            var e = document.createEventObject();
        }

        else return;

        e.datatype = dtype;
        e.data = d;

        if (target.dispatchEvent) target.dispatchEvent(e);
        else if (target.fireEvent) target.fireEvent("ondataready",e);
    },

    rx: function(target,worker){
        if (typeof target == 'string') target = document.getElementById(target);
        if (target.addEventListener)
            target.addEventListener("dataready",handler, false);
        else if (target.attachEvent)
            target.attachEvent("ondataready",handler);
    }
}

Function.prototype.after = function(g){
   var f = this;
   return function(){ f();g(); }
}   

Function.prototype.around = function(g){
	var f = this;
	return function(){ g(); f(); g(); }
}

Function.prototype.before = function(k){
	var f = this;
	return function(){ k(); f(); }
}

Function.prototype.ready = function(){
	addLoadEvent(this);
}

Mouse = {
	fire: function(id){
		var ob = document.getElementById(id);
		if (document.createEvent){
			var ev = document.createEvent('MouseEvents');
			ev.initEvent( action, true, false );
			ob.dispatchEvent(ev);
		}else if (document.createEventObject){
			ob.fireEvent('on' + action);
		}
	},

    getPos: function(e){
        var left = 0, top  = 0;
        while(e.offsetParent){
            left += e.offsetLeft;
            top  += e.offsetTop;
            e = e.offsetParent;
        }
        left += e.offsetLeft;
        top  += e.offsetTop;
        return { x:left, y:top }
    },

    mousePos: function(e){
        var e = e || window.event;
        var px = (e.pageX) 
				? e.pageX 
				: (e.clientX 
					? e.clientX + document.body.scrollLeft 
					: 0);

        var py = (e.pageY) 
			? e.pageY 
			: (e.clientY 
				? e.clientY + document.body.scrollTop 
				: 0);
        return {x:px,y:py};
    },

}

// Objects
Object.prototype.set = function(x,y){
	(this.setAttribute) ? this.setAttribute(x,y) : this[x] = y;
	return this;
}
Object.prototype.get = function(x){
	return (this.getAttribute) ? this.getAttribute(x) : this[x];
}

Stream = {
    Event: function (){
        this.listeners = [];
        this.add = function(f){
            this.listeners.push(f);
        }
        this.react = function(v){ 
            this.listeners.map(function(f){ f(v) }); 
        }
        return this;
    },

    Beh: function (e, init){
        Event.call(this);
        this.now = init;
        var me = this;
        e.listeners.push(function(v){
            if (v != me.now){
                me.react(v); me.now = v;
            }
        });
        return this;
    },

    timerE: function (ms){
        var e = new Event();
        window.setInterval( function(){ e.react(new Date().getTime()); } , ms );
        return e;
    },

    liftE: function (f,e){
        var e2 = new Event();
        e.listeners.push(function(v){ e2.react(f(v)); });
        return e2;
    },

    liftB: function (f,e){
        return new Beh(liftE(f,e), f(b.now));
    }
}


String.prototype.camelize = function(){
	var w = this;
    if (w.indexOf('-') < 1) return word;

    var aux = function(w){
        pos = w.indexOf('-');
        if (pos > 0) {
            var h = w.substring(0, pos);
            var x = w.substring(pos+1, pos+2).toUpperCase();
            var t = w.substring(pos+2);
            return "" + h + x + aux(t);
        } else {
            return w;
        }
    }
    var head = word.substring(0, init_pos)  
    return aux(t);
}

// Strings
String.prototype.basename = function(){
	if (this == '')return;
	var bits = this.split('\\');
	return bits[bits.length-1];
}

String.prototype.explode = function(){
    var positions = String.getAllIndexes('-');
    var last = 0;
    var toks = [];
    for (var p in positions) {
       toks.push(this.substring(last, p));
       last += p + 1;
    }
    return toks;
}

String.prototype.implode = function(lst){
	var first = '' + lst[0];
	var rest = lst.reduce(function(x, y){return x + '' + y});
	return first + rest;
}

String.prototype.fun = function(){
	var me = this;
	var args = 'x';
	var body = this;
	if (this.indexOf('->') > -1){
		var args = this.split('->')[0].replace(/ $/,'').replace(/ /g,',');
		var body = this.split('->')[1];
	}
	eval( "var f = function(" + args + "){ return " + body + ";};" );
	return f;
}

String.prototype.getAllIndexes = function (c) {
    var aux = function(str, c, indexes) {
        pos = str.indexOf(c);
        if (pos < 0) return indexes;
        var tail = str.substring(pos+1);
        indexes.push(pos);
        return aux(tail, c, indexes);
    }
    return aux(this, c, []);
}

String.prototype.humanize = function(){
	var cap = this.charAt(0).toUpperCase();
	var rest = this.substring(1).replace(/_/, ' ');
	return cap + rest
}

String.prototype.split = function(x, acc){
	acc = acc || [];
	var index = this.indexOf(x);
	if (this.length < x.length || index < 0) return acc.concat(this);
	acc.push(this.slice(0,index));
	var nstr = this.slice(this.indexOf(x) + x.length);
	return nstr.split(x, acc);
}

////////////////////////////////
// End of prototypes
////////////////////////////////

var now = function(){
	var cur = new Date();
	return cur.getTime();

}

var Time = {
	'curTime' : function(){
		var cur = new Date();
		return {
			'epoch'      : cur.getTime(),
			'seconds'    : ('0' + cur.getSeconds()).substring(0,2),
			'minutes'    : ('0' + cur.getMinutes()).substring(0,2),
			'hours'      : ('0' + cur.getHours()).substring(0,2),
			'week_day'   : ('0' + cur.getDay()).substring(0,2),
			'day'        : ('0' + cur.getDate()).substring(1),
			'month'      : ('0' + (1+cur.getMonth())).substring(0,2),
			'short_year' : (''  + cur.getFullYear()).substring(2),
			'long_year'  : cur.getFullYear()
		}
	}
}
var doReady = function(lst){
	lst.map("addLoadEvent(x)");
}
var pagex = function(ob){
	return (ob.offsetParent) 
		? ob.offsetLeft + pagex(ob.offsetParent) 
		: ob.offsetLeft;
}
var pagey = function(ob){
	return ob.offsetParent 
		? ob.offsetTop + (pagey(ob.offsetParent)) 
		: ob.offsetTop;
}
var Anim = {
    animclip: function(id,speed){
        var endWidth = parseInt($(id).style.width) + 10;
        var endHeight = parseInt($(id).style.height) + 10;
        var dh =  parseInt(endWidth)  / (2 * speed);
        var dv =  parseInt(endHeight) / (2 * speed);
        var left = parseInt($(id).style.left);
        var top  = parseInt($(id).style.top);
        var _anim = function(l,t,timer){
            timer['reg'] && clearInterval(timer['reg']);
            var l = l-dh; 
            var t = t-dv;
            var b = endHeight - t;
            var r = endWidth - l; 
            $(id).style.clip = "rect("+t+"px "+r+"px "+b+"px "+l+"px)";
            if ((r-l) > endWidth+10) return;
            timer['reg'] = setInterval(function(){_anim(l,t,timer)},speed);
        }
        this.act = _anim(endWidth/2,endHeight/2,{'reg':0});
    },

    anim: function(e,a,f,b,c,d,rev,then){
        var st = now(); 
        var _anim = function(f,b,c,d,timer){
            timer['reg'] && clearInterval(timer['reg']);
            var dt = (now() - st) / 1000;
            var x = f(b,dt,c,d);
            if (rev) x = c - x;
            eval("$('"+e+"')."+a+"='"+x+"px';");
            if (dt >= d) {
                then && then();
                return;
            }
            timer['reg'] = setInterval(function(){_anim(f,b,c,d,timer)},10);
        }
        this.act = _anim(f,b,c,d,{'reg':0});
    },

    lin: function(b,t,c,d){ return c * t/d + b; },

    zin: function(b,t,c,d){return c*(t/=d)*t*t+b},

    zot: function(b,t,c,d){return(-c*(t/=d)*(t-2))+b},

    zio: function(b,t,c,d){
        return((t/=d/2) < 1) ? c/2*t*t+b : -c/2*((--t)*(t-2)-1)+b;
    }
}

$A = function(x){
	var a = [];
	for(var i = 0;i < x.length;i++) a.push(x[i]);
	return a;
}

function toArray(x){return $A(x)}

$T = function(tag){ 
	return $A(document.getElementsByTagName(tag)); }

$C = function(c, xs){
	var r = new RegExp("^" + c);
	var xs = xs || $A(document.body.childNodes);
	var found = [];
	var aux = function(xs){
		xs.map(function(elm){ 
			if (r.test(elm.className)) found.push(elm); 
			if (elm.hasChildNodes())   aux($A(elm.childNodes));
		});
	}
	aux(xs);
	return found;
}
var $T = function(tag, root){
	var root = $(root) || document;
	var lst = root.getElementsByTagName(tag);
	if (!lst) return;
	return $A(lst);
}

var nextone = function(x,d){
	var n = (d == 0) ? x.previousSibling : x.nextSibling;
	if (!n) return false;
	if (n.nodeType == 1) return n;
	return nextone(n,d)
}

var oneBefore = function (x){ 
	return nextone(x,0); 
}

var oneAfter = function (x){ 
	return nextone(x,1); 
}

var give = function(ctnr, stf){ 
	ctnr.innerHTML = stf; 
}

var addInts = function(x,y){
	return parseInt(x) + parseInt(y);
}

var fold = function(f, acc, lst){
	for (var i=0, len=lst.length; x=lst[i]; i++) { 
		acc = f(lst[i], acc);
	}
	return acc;
}

var union = function(xs,ys){
	var xs = xs.sort();
	var ys = ys.sort();
	var aux = function(xs,ys,acc){
		// alert(xs + " " + ys);
		if (!xs.length || !ys.length) return acc;
		return (xs[0] < ys[0]) 
			? aux( xs.slice(1), ys, acc ) 
		    : ((xs[0] > ys[0]) 
				? aux( xs, ys.slice(1), acc ) 
				:  aux( xs.slice(1), ys.slice(1), acc.concat(xs[0]) )); 
	}
	return aux(xs,ys,[]);
}

var toggle = function(x){
	if (x) {
		x.style.display = (x.style.display == '') 
			? 'none' 
			: '';
	}
}

var splitlist = function (list, splits, property){
	var group = [];
	var curs = [];
	list.iter(function(x){
		var txt = x[property].replace(/ +$/,'');
		if (splits.length > 0) {
			if (txt == splits[0]){
				group.push(curs);
				curs = [x];
				splits = splits.slice(1);
			}else{
				curs.push(x);
			}
		}
	});
	return group;
}

var getstyle = function(x,s){
	var attr = camelize(s);
	var y = (x.currentStyle) 
        ?  x.currentStyle[attr] 
        :  document.defaultView.getComputedStyle(x,null).getPropertyValue(s);
	return y;
}

var rounded = function(n,d){
	var d = d || 2;
	var num = Math.round(n * Math.pow(10,d)) / Math.pow(10,d);
	return num;
}

var addLoadEvent = function(func){
	var	oldonload =	window.onload;
	window.onload = (typeof window.onload != 'function') 
        ? func 
        : function(){ 
			if (oldonload) {
				oldonload(); 
			}
			func();
		}
}})()
*/
