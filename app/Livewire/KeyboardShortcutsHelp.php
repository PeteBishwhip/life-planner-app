<?php

namespace App\Livewire;

use App\Services\KeyboardShortcutService;
use Livewire\Component;

class KeyboardShortcutsHelp extends Component
{
    public bool $show = false;

    protected $listeners = ['showKeyboardHelp' => 'open'];

    public function open(): void
    {
        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function render()
    {
        $shortcutService = new KeyboardShortcutService;
        $shortcuts = $shortcutService->getShortcutsByCategory();

        return view('livewire.keyboard-shortcuts-help', [
            'shortcuts' => $shortcuts,
        ]);
    }
}
