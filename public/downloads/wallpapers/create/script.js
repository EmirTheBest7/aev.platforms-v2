(function(w) {

    var logo,
    	playing = true,
    	ready = false,
		shapes = [],
    	ac = 0.09,
		de = 0.9,
		vx = 0,
		vy = 0,
		xx = 0,
		yy = 0,
    	hue = 0,
    	points = {
    		x: [[0, 0], [0, 0]],
    		y: [[0, 0], [0, 0]]
    	},
    	rect,
		pos_x,
		pos_y,
		img_data,
    	canvas, 
		ctx,
		message,
		delay,
		count,
		wrap,
		target;

	var mouse = {
	        x: 0,
	        y: 0,
	        px: 0,
	        py: 0
		};

	var demo = true,
		demo_t = 0;

	function init() {
        canvas = document.getElementById("c");
        ctx = canvas.getContext("2d");
        message = document.getElementById("message");

		target = document.getElementById("logo");
		wrap = document.createElement("div");

		wrap.appendChild(target);

		resize_handler();

        message.innerHTML = 'Click for fullscreen, click again to generate wallpaper.';

		var resize = debounce(resize_handler, 20, true);
        w.addEventListener("resize", resize);

		if (document.documentElement.requestFullscreen) {
	        document.addEventListener("fullscreenchange", resize_handler);
		}else if (document.documentElement.msRequestFullscreen) {
	        document.addEventListener("msfullscreenchange", resize_handler);
		}else if (document.documentElement.mozRequestFullScreen) {
			document.addEventListener("mozfullscreenchange", resize_handler);
		}else if (document.documentElement.webkitRequestFullScreen) {
	        document.addEventListener("webkitfullscreenchange", resize_handler);
		}

        canvas.addEventListener("mousemove", mouse_move_handler);
        canvas.addEventListener("touchmove", mouse_move_handler);
        canvas.addEventListener("click", click_handler);

        w.onload = animate;
	}

	function animate() {
		ctx.fillStyle = "rgb(0, 0, 0)";
		ctx.fillRect(0, 0, canvas.width, canvas.height);

        xx += vx += (mouse.x - xx) * ac;
        yy += vy += (mouse.y - yy) * ac;

        var len = Math.sqrt(vx * vx + vy * vy);

        for (i = 0; i < 2; i++) {
        	var x = i == 0 ? xx + 1 + len * 0.1 : xx - 1 - len * 0.1;
        	var y = i == 0 ? yy - 1 - len * 0.1 : yy + 1 + len * 0.1;
        	points.x[i].shift();
        	points.y[i].shift();
        	points.x[i].push(x);
        	points.y[i].push(y);
        }

        shapes.push({
        	px0: [points.x[0][0], points.x[0][1]],
        	py0: [points.y[0][0], points.y[0][1]],
        	px1: [points.x[1][0], points.x[1][1]],
        	py1: [points.y[1][0], points.y[1][1]]
        });

        if (shapes.length >= 50) shapes.shift();

		ctx.fillStyle = "hsla(" + (hue % 360) + ", 100%, 50%, 1)";
		ctx.strokeStyle = "hsla(" + (hue % 360) + ", 100%, 50%, 1)";

		for (var i = 0; i < shapes.length; i++){

            if (i + 2 < shapes.length - 1) {
				var x = [],
					y = [],
					x2 = [],
					y2 = [];

				var curves = [{x: [], y: []}, {x: [], y: []}];
				var u = i,
					c = 0;

				for (j = 0; j < 4; j++) {
					if (j > 1) u++;
					if (j > 0) c = 1;
			        curves[0].x.push(shapes[u].px0[c]);
			        curves[0].y.push(shapes[u].py0[c]);
				}

				for (j = 0; j < 4; j++) {
			        curves[1].x.push(shapes[u].px1[c]);
			        curves[1].y.push(shapes[u].py1[c]);
					if (j < 2) u--;
					if (j == 2) c = 0;
				}

			    curves[0].px = calc_control_points(curves[0].x);
			    curves[0].py = calc_control_points(curves[0].y);

			    curves[1].px = calc_control_points(curves[1].x);
			    curves[1].py = calc_control_points(curves[1].y);
			    
			    var c0 = curves[0],
			    	c1 = curves[1];

			    ctx.beginPath();
			    ctx.moveTo(c0.x[0], c0.y[0]);

			    for (j = 0; j < 3; j++) {
			    	ctx.bezierCurveTo(c0.px.p1[j], c0.py.p1[j], c0.px.p2[j], c0.py.p2[j], c0.x[j + 1], c0.y[j + 1]);
			    }

			    ctx.lineTo(c1.x[0], c1.y[0]);

			    for (j = 0; j < 3; j++) {
			    	ctx.bezierCurveTo(c1.px.p1[j], c1.py.p1[j], c1.px.p2[j], c1.py.p2[j], c1.x[j + 1], c1.y[j + 1]);
			    }

				ctx.lineTo(c0.x[0], c0.y[0]);

			    ctx.stroke();
			    ctx.fill();
			}

        }

        try {
        	if (ready) ctx.drawImage(logo, pos_x, pos_y);
        }catch (e) {
        	
        }
		

        vx *= de;
        vy *= de;

		hue += 4;

		if (hue > 359) t = 0;

        if (demo) {
			demo_t += 0.1;
			if (demo_t > 1) {
				demo = false;
			}else{
				mouse.x = canvas.width * Math.sin(demo_t);
				mouse.y = canvas.height * Math.cos(demo_t);
			}
        }

		if (playing) requestAnimationFrame(animate);
	}

	function calc_control_points(K) {
	    var p1 = [];
	    var p2 = [];
	    var n = K.length - 1;
	    
	    var a = [];
	    var b = [];
	    var c = [];
	    var r = [];
	    
	    a[0] = 0;
	    b[0] = 2;
	    c[0] = 1;
	    r[0] = K[0]+2*K[1];
	    
	    for (i = 1; i < n - 1; i++) {
	        a[i] = 1;
	        b[i] = 4;
	        c[i] = 1;
	        r[i] = 4 * K[i] + 2 * K[i + 1];
	    }
	            
	    a[n - 1] = 2;
	    b[n - 1] = 7;
	    c[n - 1] = 0;
	    r[n - 1] = 8 * K[n - 1] + K[n];
	    
	    for (i = 1; i < n; i++) {
	        var m = a[i] / b[i-1];
	        b[i] = b[i] - m * c[i - 1];
	        r[i] = r[i] - m * r[i - 1];
	    }
	 
	    p1[n - 1] = r[n - 1] / b[n - 1];
	    for (i = n - 2; i >= 0; --i) {
	    	p1[i] = (r[i] - c[i] * p1[i+1]) / b[i];
	    }

	    for (i = 0; i < n - 1; i++) {
	    	p2[i] = 2 * K[i + 1] - p1[i + 1];
	    }

	    p2[n - 1] = 0.5 * (K[n] + p1[n - 1]);
	    
	    return {p1:p1, p2:p2};
	}

    function mouse_move_handler(e) {
        e.preventDefault();

    	if (typeof(e.touches) != 'undefined') e = e.touches[0];

        mouse.px = mouse.x;
        mouse.py = mouse.y;
        
        mouse.x = e.pageX - rect.left;
        mouse.y = e.pageY - rect.top;
    }

    function click_handler() {
    	if (!is_fullscreen()) {
    		fullscreen();
			return;
    	}

    	cancel_download();

        playing = !playing;

        if (playing) {
        	animate();
        }else{
        	download();
        }
    }

    function cancel_download() {
    	if (delay) {
    		message.innerHTML = '';
    		clearInterval(delay);
    		delay = null;
    	}
    }

	function download () {
		count = 2;
		message.innerHTML = 'Wallpaper downloading in 3 (click to cancel)';
		delay = setInterval(function() {

			if (!is_fullscreen()) {
				
				cancel_download();

	    		if (!playing) {
	    			playing = true;
    				animate();		
    			}

				return;
			}

			if (count >= 0) {
				message.innerHTML = 'Wallpaper downloading in ' + count + ' (click to cancel)';
				count--;
				return;
			}

			cancel_download();

			window.open(canvas.toDataURL('image/png'));

			exit_fullscreen();

			playing = true;
			animate();

		}, 1000);
	}

    function is_fullscreen() {
    	return document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement;
    }	

    function fullscreen() {
		if (document.documentElement.requestFullscreen) {
		    document.documentElement.requestFullscreen();
		}else if (document.documentElement.msRequestFullscreen) {
			document.documentElement.msRequestFullscreen();
		}else if (document.documentElement.mozRequestFullScreen) {
		    document.documentElement.mozRequestFullScreen();
		}else if (document.documentElement.webkitRequestFullScreen) {
		    document.documentElement.webkitRequestFullScreen();
		}
    }

    function exit_fullscreen() {
    	if (document.exitFullscreen) {
			document.exitFullscreen();
	    } else if (document.msExitFullscreen) {
			document.msExitFullscreen();
	    } else if (document.mozCancelFullScreen) {
			document.mozCancelFullScreen();
	    } else if (document.webkitExitFullscreen) {
			document.webkitExitFullscreen();
	    }
    }

	function resize_handler() {
	    ready = false;

		if (!playing) return;

        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        rect = canvas.getBoundingClientRect();

	    target.setAttribute("width", canvas.width / 2);
	    target.setAttribute("height", canvas.height);
		
		load(wrap.innerHTML);
    }

	function debounce(func, wait, immediate) {
	    var timeout;

	    return function() {
	        var context = this, 
	            args = arguments;

	        var later = function() {
	            timeout = null;
	            if (!immediate) func.apply(context, args);
	        };

	        var callNow = immediate && !timeout;
	        clearTimeout(timeout);

	        timeout = setTimeout(later, wait);
	        if (callNow) func.apply(context, args);

	    };
	}

	function load(data) {
		var url = "data:image/svg+xml;charset=utf-8;base64," + btoa(data);
		logo = new Image();
		logo.onload = function() {
			pos_x = (canvas.width - logo.width) / 2;
		    pos_y = (canvas.height - logo.height) / 2;
			ready = true;
		};
		logo.src = url;
	}

    w.Ribbon = {
        initialize: init
    }

}(window));

var DOMURL = window.URL || window.webkitURL || window;
window.requestAnimationFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame;

Ribbon.initialize();