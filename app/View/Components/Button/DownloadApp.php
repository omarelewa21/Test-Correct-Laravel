<?php

namespace tcCore\View\Components\Button;

use Illuminate\View\Component;
use tcCore\Http\Helpers\AppVersionDetector;

class DownloadApp extends Component
{
    public string $appStoreLink;
    public string $iconName;

    public function __construct(
        public $size = 'sm',
        public $rotateIcon = false,
        public $selid = 'download-app-from-store',
        public $disabled = false
    ) {
        $this->appStoreLink = AppVersionDetector::osIsMac()
            ? 'https://apps.apple.com/nl/app/test-correct/id1478736834?l=en'
            : 'https://www.microsoft.com/en-us/p/test-correct/9p5knbs4r6n0?activetab=pivot:overviewtab';
        $this->iconName = AppVersionDetector::osIsMac() ? 'apple' : 'windows';
    }

    public function render()
    {
        return 'components.button.download-app';
    }
}
