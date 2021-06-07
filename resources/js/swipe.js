let touchstartX = 0;
let touchstartY = 0;
let touchendX = 0;
let touchendY = 0;
let disableSwipeForElements = ['.disable-swipe-navigation', '#navigation-container', '.ranking', '.classify', '.matching'];
let swipeAccuracyDefer = 100;

const gestureZone = document.getElementById('body');

gestureZone.addEventListener('touchstart', function (event) {
    touchstartX = event.changedTouches[0].screenX;
    touchstartY = event.changedTouches[0].screenY;
}, false);

gestureZone.addEventListener('touchend', function (event) {
    touchendX = event.changedTouches[0].screenX;
    touchendY = event.changedTouches[0].screenY;
}, false);

handleGesture = function (target) {
    if (!shouldSwipeDirectionBeReturned(target)) return;

    if (diff(touchendY, touchstartY) > swipeAccuracyDefer && touchendY < touchstartY) {
        return 'down';
    }

    if (diff(touchendY, touchstartY) > swipeAccuracyDefer && touchendY > touchstartY) {
        //Swipe up
        return 'up'
    }

    if (diff(touchendX, touchstartX) > swipeAccuracyDefer && touchendX < touchstartX) {
        //Swipe left
        return 'left';
    }

    if (diff(touchendX, touchstartX) > swipeAccuracyDefer && touchendX > touchstartX) {
        //Swipe right
        return 'right';
    }

    if (touchendY === touchstartY) {
        //Tap
        return 'tab'
    }
}

function diff(a, b) {
    return Math.abs(a - b);
}

function shouldSwipeDirectionBeReturned(target) {
    let returnDirection = true;

    disableSwipeForElements.forEach(function (el) {
        if (target.closest(el) !== null) {
            returnDirection = false;
        }
    });

    return returnDirection;
}