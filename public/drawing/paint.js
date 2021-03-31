(function() {
'use strict ';
/*!
 * Paint
 * Drawing on canvases simplified
 * 
 * Copyright 2014 - Webbix Creative Solutions
 */

var Paint = {
	defaults: {
		canvas: {
			background: '#fff'
		}
	},
	width : null,
};

Paint.setWidth = function(newWidth){
	this.width = newWidth;
}

Paint.getWidth = function(){
	return this.width;
}

var layers = 0;

window.Paint = Paint;
/****************************************************************
 * Paint.BezierCurve
 * Create a new point that draws a bezier line
 *
 * @constructor
 * @param {Number} x
 * @param {Number} y
 ****************************************************************/

Paint.BezierCurve = function(cp1, cp2, p) {
	this.cp1 = cp1 || new Paint.Point;
	this.cp2 = cp2 || new Paint.Point;
	this.point = p || new Paint.Point;
};
/****************************************************************
 * Paint.Canvas
 * Initialise a new canvas
 *
 * @constructor
 * @param {Object} options
 ****************************************************************/

Paint.Canvas = function(options) {
	Paint.Layer.call(this, options);
	
	var _this = this,
		canvas = this.getCanvas(),
		context = this.getContext(),
		grid = new Paint.Layer(),
		events = {},
		width = null;
	

	this.rerender = function(newWidth) {
		Paint.setWidth(newWidth);
		this.render();
	}
	/****************************************************************
	 * Paint.Canvas.undo
	 * Undo last draw
	 ****************************************************************/
	
	this.undo = function() {

		if(layers > 0) {
			this.getEnabledChildren().pop().enabled = false;
			this.render();

			layers--;
		}
	};
	
	
	/****************************************************************
	 * Paint.Canvas.redo
	 * Redo last draw
	 ****************************************************************/
	
	this.redo = function() {
		if(this.getDisabledChildren().length > 0) {
			this.getDisabledChildren().shift().enabled = true;
			this.render();
			layers++;
		}
	};

	
	/****************************************************************
	 * Paint.Canvas.showGrid
	 * Show grid
	 * 
	 * @param {Object} divisions
	 * @param {String} color
	 * @param {Number} width
	 ****************************************************************/
	
	this.showGrid = function(divisions, color, width) {

		if(divisions.x == 1) {
			divisions.x += 2;
		}else if(divisions.x == 2) {
			divisions.x += 3;
		}else if(divisions.x == 3) {
			divisions.x += 4;
		}else if(divisions.x == 3) {
			divisions.x += 5;
		}else if(divisions.x == 4) {
			divisions.x += 6;
		}else if(divisions.x == 5) {
			divisions.x += 7;
		}else if(divisions.x == 6) {
			divisions.x += 8;
		}else if(divisions.x == 7) {
			divisions.x += 9;
		}

		var x, y, path,
			
			xd = canvas.width / (divisions.x + 1),
			yd = canvas.height / (divisions.y + 1),
			
			style = {
				line: {
					style: color || 'rgba(0, 0, 0, 0.25)',
					width: width || 1
				}
			};

		
		grid.enabled = true;
		grid.clear();
		
		for(x = 1; x < divisions.x + 1; x++) {
			path = new Paint.Path(style);
			path.add(new Paint.Point(xd * x, 0));
			path.add(new Paint.Point(xd * x, canvas.height));
			
			grid.add(path);
		}
		
		for(y = 1; y < divisions.y + 1; y++) {
			path = new Paint.Path(style);
			path.add(new Paint.Point(0, yd * y));
			path.add(new Paint.Point(canvas.width, yd * y));
			
			grid.add(path);
		}
		
		this.render();
	};
	
	
	/****************************************************************
	 * Paint.Canvas.hideGrid
	 * Hides grid
	 ****************************************************************/
	
	this.hideGrid = function() {
		grid.enabled = false;
	};
	
	
	/****************************************************************
	 * Paint.Canvas.on
	 * Add event handler
	 ****************************************************************/
	
	this.on = function(event, handler) {
		// Split events and handle them seperately
		if(event.indexOf(' ') >= 0) {
			event.split(' ').forEach(function(e) {
				_this.on(e, handler);
			});
			
			return;
		}
		
		// Add even if it did not exist
		if(!events[event]) {
			events[event] = [];
			
			canvas.addEventListener(event, function(e) {
				var i = events[event].length;
				while(i--) events[event][i].call(this, e);
			});
		}
		
		events[event].push(handler);
	};
	
	
	/****************************************************************
	 * Initialisation
	 ****************************************************************/
	
	this.add(grid);
	this.render();
};
/****************************************************************
 * Paint.Image
 * An Image object
 *
 * @constructor
 * @param {Object} image
 * @param {Object} options
 ****************************************************************/

Paint.Image = function(image, options) {
	if(!options) options = {};
	
	this.position = options.position || new Paint.Point;
	this.size = options.size || new Paint.Point(image.width || 0, image.height || 0);
	
	
	/****************************************************************
	 * Paint.Image.render
	 * Render the image
	 *
	 * @param {Object} canvas
	 * @param {Object} context
	 ****************************************************************/
	
	this.render = function(canvas, context) {
		if(Paint.getWidth() != null) {
			canvas.width = Paint.getWidth();
		}

		context.drawImage(
			image,
			this.position.x,	this.position.y,
			this.size.x,		this.size.y);
	};
};
/****************************************************************
 * Paint.Layer
 * Create a new layer
 *
 * @constructor
 * @param {Object} options
 ****************************************************************/

Paint.Layer = function(options) {
	if(!options) options = {};
	
	var canvas = document.createElement('canvas'),
		context = canvas.getContext('2d'),
		children = [];

	this.enabled = true;
	
	
	/****************************************************************
	 * Paint.Layer.add
	 * Add child to layer
	 *
	 * @param {Object} child
	 ****************************************************************/
	
	this.add = function(child, note) {

		if(note == true) {
			layers++;
		}

		children.push(child);
	};
	
	
	/****************************************************************
	 * Paint.Layer.remove
	 * Remove child from layer
	 *
	 * @param {Object} child
	 ****************************************************************/
	
	this.remove = function(child) {
		var idx;
		
		if((idx = children.indexOf(child)) > -1) {
			children.splice(idx, 1);
		}
	};
	
	
	/****************************************************************
	 * Paint.Layer.clear
	 * Clear layer
	 ****************************************************************/
	
	this.clear = function() {
		children.length = 0;
	};

	
	/****************************************************************
	 * Paint.Layer.render
	 * Renders children
	 ****************************************************************/
	
	this.render = function(pcanvas, pcontext) {
		if(!this.enabled) return;
		
		var i;
		
		// Reset canvas size
		canvas.height = pcanvas ? pcanvas.height : options.height;
		canvas.width = pcanvas ? pcanvas.width : options.width;

		if(Paint.getWidth() != null) {
			canvas.width = Paint.getWidth();
		}
		if (canvas.width === 0) {
			canvas.width = Paint.getWidth();
		}
		// Clear canvas
		context.clearRect(0, 0, canvas.width, canvas.height);
		
		// Draw children
		for(i = 0; i < children.length; i++) {
			children[i].render(canvas, context);
		}
		
		// Draw to parent

		if(pcontext) {
			pcontext.drawImage(canvas, 0, 0);
		}
	};
	
	
	/****************************************************************
	 * Paint.Layer.getEnabledChildren
	 * Returns a list of layers that are enabled
	 ****************************************************************/
	
	this.getEnabledChildren = function() {
		return children.filter(function(l) { return l.enabled; });
	};
	
	
	/****************************************************************
	 * Paint.Layer.getDisabledChildren
	 * Returns a list of layers that are disabled
	 ****************************************************************/
	
	this.getDisabledChildren = function() {
		return children.filter(function(l) { return !l.enabled; });
	};
	
	
	/****************************************************************
	 * Paint.Layer.getCanvas
	 * Returns canvas element
	 ****************************************************************/
	
	this.getCanvas = function() {
		return canvas;
	};
	
	
	/****************************************************************
	 * Paint.Layer.getContext
	 * Returns context
	 ****************************************************************/
	
	this.getContext = function() {
		return context;
	};
	
	
	/****************************************************************
	 * Paint.Layer.getChildren
	 * Returns context
	 ****************************************************************/
	
	this.getChildren = function() {
		return children;
	};

	this.toggleChild = function(index) {

		index--;

		if(children[index].enabled) {
			children[index].enabled = false;
		}else{
			children[index].enabled = true;
		}

		this.render();
	}
};
/****************************************************************
 * Paint.Path
 * An Path object
 *
 * @constructor
 * @param {Object} points
 * @param {Object} options
 ****************************************************************/

Paint.Path = function(options) {
	var points = [],
		renderOptions = new Paint.RenderOptions(options);
	
	if(!options) options = {};
	this.position = options.position || new Paint.Point;
	this.rotation = 0;
	
	
	/****************************************************************
	 * Paint.Image.add
	 * Adds a point to the path
	 ****************************************************************/
	
	this.add = function(point) {
		points.push(point);
	};
	
	
	/****************************************************************
	 * Paint.Image.remove
	 * Removes a point from the path
	 ****************************************************************/
	
	this.remove = function(point) {
		this.removeAt(points.indexOf(point));
	};
	
	
	/****************************************************************
	 * Paint.Image.removeLast
	 * Removes last point from the path
	 ****************************************************************/
	
	this.removeLast = function() {
		points.unshift();
	};
	
	
	/****************************************************************
	 * Paint.Image.removeAt
	 * Removes specific point from the path
	 ****************************************************************/
	
	this.removeAt = function(n) {
		points.splice(n, 1);
	};
	
	
	/****************************************************************
	 * Paint.Image.render
	 * Render the image
	 *
	 * @param {Object} canvas
	 * @param {Object} context
	 ****************************************************************/
	
	this.render = function(canvas, context) {

		if(Paint.getWidth() != null) {
			canvas.width = Paint.getWidth();
		}
		var i;
		
		context.beginPath();
		
		context.translate(this.position.x, this.position.y);
		context.rotate(this.rotation);
		
		for(i = 0; i < points.length; i++) {
			if(i === 0) context.moveTo(
				points[i].x,
				points[i].y
			);
			
			if(points[i] instanceof Paint.Point) {
				context.lineTo(
					points[i].x,
					points[i].y
				);
			} else if(points[i] instanceof Paint.BezierCurve) {
				context.bezierCurveTo(
					points[i].cp1.x,
					points[i].cp1.y,
					
					points[i].cp2.x,
					points[i].cp2.y,
					
					points[i].point.x,
					points[i].point.y);
			}
		}
		
		if(options.close) {
			context.closePath();
		}
		
		renderOptions.render(canvas, context);
		context.setTransform(1, 0, 0, 1, 0, 0);
	};
};

Paint.Path.getCircle = function(scale, options) {
	var offset = (4 / 3) * Math.tan(Math.PI / 8),
		path = new Paint.Path(options);
	
	if(typeof scale === 'number') {
		scale = new Paint.Point(scale, scale);
	}
	
	// Right
	path.add(new Paint.Point(scale.x, 0));
	
	// Bottom
	path.add(new Paint.BezierCurve(
		new Paint.Point(scale.x, offset * scale.y),
		new Paint.Point(offset * scale.x,  scale.y),
		new Paint.Point(0, scale.y)
	));
	
	// Left
	path.add(new Paint.BezierCurve(
		new Paint.Point(-offset * scale.x, scale.y),
		new Paint.Point(-scale.x, offset * scale.y),
		new Paint.Point(-scale.x, 0)
	));
	
	// Top
	path.add(new Paint.BezierCurve(
		new Paint.Point(-scale.x, -offset * scale.y),
		new Paint.Point(-offset * scale.x,  -scale.y),
		new Paint.Point(0, -scale.y)
	));
	
	// Close
	path.add(new Paint.BezierCurve(
		new Paint.Point(offset * scale.x, -scale.y),
		new Paint.Point(scale.x, -offset * scale.y),
		new Paint.Point(scale.x, 0)
	));
	
	return path;
};

Paint.Path.getRectangle = function(scale, options) {
	var path = new Paint.Path(options);
	
	if(typeof scale === 'number') {
		scale = new Paint.Point(scale, scale);
	}
	
	path.add(new Paint.Point(-0.5 * scale.x, -0.5 * scale.y));
	path.add(new Paint.Point( 0.5 * scale.x, -0.5 * scale.y));
	path.add(new Paint.Point( 0.5 * scale.x,  0.5 * scale.y));
	path.add(new Paint.Point(-0.5 * scale.x,  0.5 * scale.y));
	path.add(new Paint.Point(-0.5 * scale.x, -0.5 * scale.y));
	
	return path;
};

Paint.Path.getTriangle = function(scale, options) {
	var path = new Paint.Path(options);
	
	if(typeof scale === 'number') {
		scale = new Paint.Point(scale, scale);
	}
	
	path.add(new Paint.Point(0, -1 * scale.y));
	path.add(new Paint.Point(-0.5 * scale.x, 0));
	path.add(new Paint.Point( 0.5 * scale.x, 0));
	path.add(new Paint.Point(0, -1 * scale.y));
	
	return path;
};
/****************************************************************
 * Paint.Point
 * Create a new point
 *
 * @constructor
 * @param {Number} x
 * @param {Number} y
 ****************************************************************/

Paint.Point = function(x, y) {
	this.x = x || 0;
	this.y = y || 0;
};
/****************************************************************
 * Paint.RenderOptions
 * An Path object
 *
 * @constructor
 * @param {Object} options
 ****************************************************************/

Paint.RenderOptions = function(options) {
	options = options || {};
	options.line = options.line || {};
	
	/****************************************************************
	 * Paint.RenderOptions.render
	 * Render the path
	 *
	 * @constructor
	 * @param {Object} canvas
	 * @param {Object} context
	 ****************************************************************/
	
	this.render = function(canvas, context) {
		context.fillStyle		= options.fill				|| '#000';
		context.lineCap			= options.line.cap			|| 'butt';
		context.lineDashOffset	= options.line.dashOffset	|| 0;
		context.lineJoin		= options.line.join			|| 'miter';
		context.strokeStyle		= options.line.style		|| '#000';
		context.lineWidth		= options.line.width		|| 0;

		// Fill
		if(
			options.fill &&
			options.fill !== 'transparent'
		) context.fill();
		
		// Stroke
		if(
			options.line.style &&
			options.line.style !== 'transparent' &&
			options.line.width > 0
		) {
			context.setLineDash(options.line.dash || []);
			context.stroke();
		}
	};
};
})()