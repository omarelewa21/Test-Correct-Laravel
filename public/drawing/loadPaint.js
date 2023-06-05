Object.clone = function (obj) {
    var clone = {};

    for (var k in obj) {
        if (typeof obj[k] === 'object' && obj[k] !== null) {
            clone[k] = Object.clone(obj[k]);
        } else {
            clone[k] = obj[k];
        }
    }

    return clone;
};

var App = function (prefix, width, backgroundImage) {
    var mouseStart, layer, path,
        // Editor
        tool = 'line',
        style = {
            line: {
                style: '#000',
                width: 1
            }
        },

        // DOM and shit
        background = new Paint.Layer(),
        holder = document.getElementById(prefix + 'canvas-holder'),
        drawingModalContainer = document.getElementById(prefix+'drawing-question-modal-container'),
        canvas = new Paint.Canvas({
            height: 481,
            width: width
        }),
        _this = this;


    this.AddLayer = function (type) {
        var html = $('#'+prefix + 'layers-holder').html();

        var index = canvas.getChildren().length;

        html += "<div class='"+prefix + "layer' style='border-bottom: 1px solid gray; padding: 5px 10px;' data-index='" + index + "'><span class='fa fa-eye' style='float:right;'></span> " + type + "</div>";

        $('#' + prefix + 'layers-holder').html(html);

        $('.'+prefix + 'layer').click(function () {
            var index = $(this).attr('data-index');
            canvas.toggleChild(index);

            if ($(this).css('opacity') == 1) {
                $(this).css('opacity', .5);
            } else {
                $(this).css('opacity', 1);
            }
        });
    };

    /****************************************************************
     * FREEFORM
     ****************************************************************/

    this.freeformStart = function (e) {
        path = new Paint.Path(Object.clone(style));
        path.add(new Paint.Point(
            e.pageX - holder.getBoundingClientRect().left,
            e.pageY - holder.getBoundingClientRect().top
        ));

        layer = new Paint.Layer();
        layer.add(path, true);

        canvas.add(layer);
    };

    this.freeformMove = function (e) {
        if (!path || !layer) return;

        path.add(new Paint.Point(
            e.pageX - holder.getBoundingClientRect().left,
            e.pageY - holder.getBoundingClientRect().top
        ));

        canvas.render();
    };

    this.freeformStop = function () {
        if (!path || !layer) return;

        path = null;
        layer = null;

        this.AddLayer('Potlood');

    }.bind(this);


    /****************************************************************
     * LINE
     ****************************************************************/

    this.lineStart = function (e) {
        path = new Paint.Path(Object.clone(style));
        path.add(new Paint.Point(
            e.pageX - holder.getBoundingClientRect().left,
            e.pageY - holder.getBoundingClientRect().top
        ));

        layer = new Paint.Layer();
        layer.add(path, true);

        canvas.add(layer);
    };

    this.lineMove = function (e) {
        if (!path || !layer) return;

        path.removeAt(1);

        path.add(new Paint.Point(
            e.pageX - holder.getBoundingClientRect().left,
            e.pageY - holder.getBoundingClientRect().top
        ));

        canvas.render();
    };

    this.lineStop = function (title) {
        if (!path || !layer) return;

        path = null;
        layer = null;

        if (title != 'Pijl') {
            this.AddLayer('Lijn');
        } else {
            this.AddLayer(title);
        }
    }.bind(this);


    /****************************************************************
     * ARROW
     ****************************************************************/

    this.arrowStart = function (e) {
        _this.lineStart.call(this, e);

        mouseStart = new Paint.Point(
            e.pageX - holder.getBoundingClientRect().left,
            e.pageY - holder.getBoundingClientRect().top
        );
    };

    this.arrowMove = function (e) {
        if (!path || !layer) return;
        _this.lineMove.call(this, e);
    };

    this.arrowStop = function (e) {
        if (!path || !layer) return;

        var x = e.pageX - holder.getBoundingClientRect().left,
            y = e.pageY - holder.getBoundingClientRect().top,

            xd = mouseStart.x - x,
            yd = mouseStart.y - y,

            point = Paint.Path.getTriangle(10, Object.clone(style));

        point.position.x = x;
        point.position.y = y;
        point.rotation = Math.atan2(yd, xd) - Math.PI / 2;

        layer.add(point, true);

        _this.lineStop.call(this, 'Pijl');
        canvas.render();

        path = null;
        layer = null;

    };


    /****************************************************************
     * CIRCLE
     ****************************************************************/

    this.circleStart = function (e) {
        mouseStart = new Paint.Point(
            e.pageX - holder.getBoundingClientRect().left,
            e.pageY - holder.getBoundingClientRect().top
        );

        layer = new Paint.Layer();
        canvas.add(layer, true);
    };

    this.circleMove = function (e) {
        if (!layer) return;

        var x = e.pageX - holder.getBoundingClientRect().left,
            y = e.pageY - holder.getBoundingClientRect().top,

            xd = x - mouseStart.x,
            yd = y - mouseStart.y,

            scale = new Paint.Point(xd, yd),
            dist = Math.sqrt(xd * xd + yd * yd);

        // Uniform scaling
        // path = Paint.Path.getCircle(dist, style);

        // Not so uniform
        path = Paint.Path.getCircle(scale, Object.clone(style));
        path.position = mouseStart;

        layer.clear();
        layer.add(path, true);

        canvas.render();
    };

    this.circleStop = function () {
        if (!path || !layer) return;

        path = null;
        layer = null;

        this.AddLayer('Cirkel');
    }.bind(this);


    /****************************************************************
     * RECTANGLE
     ****************************************************************/

    this.rectangleStart = function (e) {
        mouseStart = new Paint.Point(
            e.pageX - holder.getBoundingClientRect().left,
            e.pageY - holder.getBoundingClientRect().top
        );

        layer = new Paint.Layer();
        canvas.add(layer, true);
    };

    this.rectangleMove = function (e) {
        if (!layer) return;

        var x = e.pageX - holder.getBoundingClientRect().left,
            y = e.pageY - holder.getBoundingClientRect().top,

            xd = x - mouseStart.x,
            yd = y - mouseStart.y,

            scale = new Paint.Point(xd, yd),
            dist = Math.sqrt(xd * xd + yd * yd);

        // Uniform scaling
        // path = Paint.Path.getCircle(dist, style);

        // Not so uniform
        path = Paint.Path.getRectangle(scale, Object.clone(style));
        path.position = mouseStart;

        layer.clear();
        layer.add(path, true);

        canvas.render();
    };

    this.rectangleStop = function () {
        if (!path || !layer) return;

        path = null;
        layer = null;

        this.AddLayer('Vierkant');
    }.bind(this);

    /****************************************************************
     * INITIALISATION
     ****************************************************************/

    document.getElementById(prefix + 'btn-tool-freeform').onclick =
        document.getElementById(prefix + 'btn-tool-freeform').ontouchdown = function () {
            tool = 'freeform';
        };


    document.getElementById(prefix + 'btn-tool-line').onclick =
        document.getElementById(prefix + 'btn-tool-line').ontouchdown = function () {
            tool = 'line';
        };

    document.getElementById(prefix + 'btn-tool-arrow').onclick =
        document.getElementById(prefix + 'btn-tool-arrow').ontouchdown = function () {
            tool = 'arrow';
        };

    document.getElementById(prefix + 'btn-tool-shape-circle').onclick =
        document.getElementById(prefix + 'btn-tool-shape-circle').ontouchdown = function () {
            tool = 'circle';
        };

    document.getElementById(prefix + 'btn-tool-shape-rectangle').onclick =
        document.getElementById(prefix + 'btn-tool-shape-rectangle').ontouchdown = function () {
            tool = 'rectangle';
        };

    /*document.getElementById('btn-undo').onclick =
    document.getElementById('btn-undo').ontouchdown = function() { canvas.undo(); };

    document.getElementById('btn-redo').onclick =
    document.getElementById('btn-redo').ontouchdown = function() { canvas.redo(); };*/


    $('#'+prefix + 'btn-thick-1').bind('click ontouchdown', function () {
        style = Object.create(style);
        style.line.width = 1;
    });

    $('#'+prefix + 'btn-thick-2').bind('click ontouchdown', function () {
        style = Object.create(style);
        style.line.width = 2;
    });

    $('#'+prefix + 'btn-thick-3').bind('click ontouchdown', function () {
        style = Object.create(style);
        style.line.width = 3;
    });

    $('.'+prefix +'colorBtn').bind('click ontouchdown', function () {
        $('.'+prefix + 'colorBtn').css({
            'opacity': 0.3
        });
        $(this).css({
            'opacity': 1
        });

        style = Object.create(style);
        style.line.style = $(this).css('background-color');
    });

    style.line.width = 2;


    if (document.getElementById(prefix + 'btn-image') != undefined) {
        document.getElementById(prefix + 'btn-image').onchange = function (e) {
            document.getElementById(prefix + 'FormBackground').submit();

            var file = this.files[0],
                reader = new FileReader();

            reader.onloadend = function (e) {
                var img = new Image(),
                    element = canvas.getCanvas();

                img.src = reader.result;

                var size = new Paint.Point(
                    970,
                    475
                    ),
                    position = new Paint.Point(
                        0,
                        0
                    );

                background.clear();
                background.add(new Paint.Image(img, {
                    position: position,
                    size: size
                }));

                canvas.render();
            };

            reader.readAsDataURL(file);
        };
    }

    if (backgroundImage !== '') {
        var img = new Image(), element = canvas.getCanvas();

        img.src = backgroundImage;

        var size = new Paint.Point(970, 475),
            position = new Paint.Point(0, 0);

        background.clear();
        background.add(new Paint.Image(img, {
            position: position,
            size: size
        }));

        canvas.render();
    }

    this.getActiveImageBase64Encoded = function() {
        return canvas.getCanvas().toDataURL();
    }
    this.rerender = function(newWidth) {
        canvas.rerender(newWidth);
    }

    document.getElementById(prefix + 'btn-export').onclick =
        document.getElementById(prefix + 'btn-export').ontouchdown = function () {
            parent.skip = true;
            var element = canvas.getCanvas();

            $.post(window.parent.drawingSaveUrl,
                {
                    drawing: element.toDataURL(),
                    additional_text: $('#'+prefix + 'additional_text').val()
                },
                function (response) {
                    if (response == 1) {
                        window.parent.Loading.hide();
                        window.parent.drawingCallback();
                    } else {
                        Notify.notify('Er ging iets mis', 'error');
                    }
                }
            );
        };


    canvas.on('mousedown touchstart', function (e) {
        var func = tool + 'Start';

        if (typeof _this[func] === 'function') {
            _this[func].call(this, e);
        }
    });

    canvas.on('mousemove touchmove', function (e) {
        var func = tool + 'Move';

        if (typeof _this[func] === 'function') {
            _this[func].call(this, e);
        }
    });

    canvas.on('mouseup mouseleave touchend touchleave', function (e) {
        var func = tool + 'Stop';

        if (typeof _this[func] === 'function') {
            _this[func].call(this, e);
        }
    });

    this.drawGrid = function(grid) {
        grid = parseInt(grid);
        canvas.showGrid(new Paint.Point(grid, grid), 'rgba(0, 0, 0, 0.2)');
    }

    canvas.add(background);

    holder.appendChild(canvas.getCanvas());

    drawingModalContainer.ontouchmove = function (e) {
        e.preventDefault();
    };
};

