var mousePressed = false;
var touchPressed = false;
var lastX, lastY;
var ctx;

function InitThis() {
    ctx = document.getElementById('canvas_firma').getContext("2d");

    $('#canvas_firma').mousedown(function (e) {
        e.preventDefault();
        e.stopPropagation();
        console.log(e)
        mousePressed = true;
        Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, false);
    });

    $('#canvas_firma').on('touchmove', function (e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('x')
        var touch = e.touches[0];
        /* var mouseEvent = new MouseEvent("mousemove", {
            clientX: touch.clientX,
            clientY: touch.clientY,
            pageX: touch.pageX,
            pageY: touch.pageY
          });
        document.querySelector("#canvas_firma").dispatchEvent(mouseEvent); */
        if(touchPressed)
            Draw(touch.pageX - $(this).offset().left, touch.pageY - $(this).offset().top, true);

    });
    $('#canvas_firma').on('touchstart', function (e) {
        touchPressed = true;
        var touch = e.touches[0];
        Draw(touch.pageX - $(this).offset().left, touch.pageY - $(this).offset().top, false);
    });
    $('#canvas_firma').on('touchend', function (e) {
        touchPressed = false;
    });


    /* canvas.addEventListener("touchmove", function (e) {
        var touch = e.touches[0];
        var mouseEvent = new MouseEvent("mousemove", {
          clientX: touch.clientX,
          clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
      }, false); */

    $('#canvas_firma').mousemove(function (e) {
        e.preventDefault();
        e.stopPropagation();
        console.log(e)

        if (mousePressed) {
            Draw(e.pageX - $(this).offset().left, e.pageY - $(this).offset().top, true);
        }
    });

    $('#canvas_firma').mouseup(function (e) {
        mousePressed = false;
    });

	$('#canvas_firma').mouseleave(function (e) {
        mousePressed = false;
    });
}
InitThis()

function Draw(x, y, isDown) {
    if (isDown) {
        ctx.beginPath();
        ctx.strokeStyle = "black";
        ctx.lineWidth = "1";
        ctx.lineJoin = "round";
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(x, y);
        ctx.closePath();
        ctx.stroke();
    }
    lastX = x; lastY = y;
}
	
function clearArea() {
    // Use the identity matrix while clearing the canvas
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
}