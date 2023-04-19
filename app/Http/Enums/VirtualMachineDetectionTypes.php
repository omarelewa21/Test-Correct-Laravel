<?php

namespace tcCore\Http\Enums;

enum VirtualMachineDetectionTypes: string {
    case windows = 'windows';
    case macos = 'macos';
    case hid = 'hid';
}
