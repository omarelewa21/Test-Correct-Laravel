let touchstartX = 0;
let touchstartY = 0;
let touchendX = 0;
let touchendY = 0;

const gestureZone = document.getElementById('body');

gestureZone.addEventListener('touchstart', function(event) {
    touchstartX = event.changedTouches[0].screenX;
    touchstartY = event.changedTouches[0].screenY;
}, false);

gestureZone.addEventListener('touchend', function(event) {
    touchendX = event.changedTouches[0].screenX;
    touchendY = event.changedTouches[0].screenY;
}, false);

handleGesture = function(target) {
    if (target.closest('#navigation-container') !== null) {
        return;
    }

    if (diff(touchendY, touchstartY) > 50 && touchendY < touchstartY) {
        return 'down';
    }

    if (diff(touchendY, touchstartY) > 50 && touchendY > touchstartY) {
        //Swipe up
        return 'up'
    }

    if (diff(touchendX, touchstartX) > 50 && touchendX < touchstartX) {
        //Swipe left
        return 'left';
    }

    if (diff(touchendX, touchstartX) > 50 && touchendX > touchstartX) {
        //Swipe right
        return 'right';
    }

    if (touchendY === touchstartY) {
        //Tap
        return 'tab'
    }
}

function diff(a,b) {return Math.abs(a-b);}