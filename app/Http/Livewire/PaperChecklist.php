<?php

namespace App\Http\Livewire;

use App\PaperChecklist as Checklist;
use Livewire\Component;

class PaperChecklist extends Component
{
    public $checklist;

    public function mount(Checklist $checklist)
    {
        $this->checklist = $checklist;
    }

    public function render()
    {
        return view('livewire.paper-checklist');
    }
}
