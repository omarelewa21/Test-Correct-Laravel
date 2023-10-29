<?php

namespace tcCore\Http\Enums;

use tcCore\Http\Enums\Attributes\Description;
use tcCore\Http\Enums\Traits\WithAttributes;

enum TestTakeEventTypes: int
{
    use WithAttributes;

    #[Description('test-take-event-types.start-test')]
    case StartTest = 1;
    #[Description('test-take-event-types.stop-test')]
    case StopTest = 2;
    #[Description('test-take-event-types.lost-focus')]
    case LostFocus = 3;
    #[Description('test-take-event-types.screenshot')]
    case Screenshot = 4;
    #[Description('test-take-event-types.started-late')]
    case StartedLate = 5;
    #[Description('test-take-event-types.start-discussion')]
    case StartDiscussion = 6;
    #[Description('test-take-event-types.end-discussion')]
    case EndDiscussion = 7;
    #[Description('test-take-event-types.continue')]
    case Continue = 8;
    #[Description('test-take-event-types.application-closed')]
    case ApplicationClosed = 9;
    #[Description('test-take-event-types.alt-tab')]
    case AltTab = 10;
    #[Description('test-take-event-types.before-input-meta')]
    case BeforeInputMeta = 11;
    #[Description('test-take-event-types.before-input-alt')]
    case BeforeInputAlt = 12;
    #[Description('test-take-event-types.alt-f4')]
    case AltF4 = 13;
    #[Description('test-take-event-types.blur')]
    case Blur = 14;
    #[Description('test-take-event-types.hide')]
    case Hide = 15;
    #[Description('test-take-event-types.minimize')]
    case Minimize = 16;
    #[Description('test-take-event-types.move')]
    case Move = 17;
    #[Description('test-take-event-types.leave-full-screen')]
    case LeaveFullScreen = 18;
    #[Description('test-take-event-types.always-on-top-changed')]
    case AlwaysOnTopChanged = 19;
    #[Description('test-take-event-types.resize')]
    case Resize = 20;
    #[Description('test-take-event-types.session-end')]
    case SessionEnd = 21;
    #[Description('test-take-event-types.printscreen')]
    case Printscreen = 22;
    #[Description('test-take-event-types.other-window-on-top')]
    case OtherWindowOnTop = 23;
    #[Description('test-take-event-types.ctrl-key')]
    case CtrlKey = 24;
    #[Description('test-take-event-types.illegal-programs')]
    case IllegalPrograms = 25;
    #[Description('test-take-event-types.rejoined')]
    case Rejoined = 26;
    #[Description('test-take-event-types.hid')]
    case Hid = 27;
    #[Description('test-take-event-types.vm')]
    case Vm = 28;

}
