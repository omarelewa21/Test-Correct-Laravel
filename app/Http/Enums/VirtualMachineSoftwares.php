<?php

namespace tcCore\Http\Enums;

enum VirtualMachineSoftwares: string
{

    case vmware = 'VMWare';
    case virtualbox = 'Virtual Box';
    case qemu = 'QEMU';
    case wine = 'Wine';
    case sandboxie = 'Sandboxie';
    case parallels = 'Parallels';
    case mshyperv = 'Microsoft Hyper-V';
    case xen = 'Xen';
    case unknown = '???';
}

enum VirtualMachineDetectionTypes: string {
    case windows = 'windows';
    case macos = 'macos';
    case hid = 'hid';
}