<?php

namespace tcCore\Http\Enums;

enum VirtualMachineSoftwares: string
{

    case vmware = 'VMWare';
    case virtualbox = 'VirtualBox';
    case qemu = 'QEMU';
    case wine = 'Wine';
    case sandboxie = 'Sandboxie';
    case parallels = 'Parallels';
    case mshyperv = 'Microsoft Hyper-V';
    case xen = 'Xen';
    case macosvm = 'macOS VM';
    case unknown = '???';
}
