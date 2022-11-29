<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;
use tcCore\FileManagement;

class FileManagementCard extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        public FileManagement $file
    ) {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.grid.file-management-card');
    }
}
