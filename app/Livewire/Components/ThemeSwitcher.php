<?php

namespace App\Livewire\Components;

use Livewire\Component;

class ThemeSwitcher extends Component
{
    public string $currentTheme = 'light';
    public bool $autoTheme = false;

    public array $themes = [
        'light' => [
            'name' => 'Light',
            'description' => 'Clean and bright',
            'preview' => 'from-gray-100 to-white'
        ],
        'dark' => [
            'name' => 'Dark',
            'description' => 'Easy on the eyes',
            'preview' => 'from-gray-800 to-black'
        ],
        'cupcake' => [
            'name' => 'Cupcake',
            'description' => 'Sweet and colorful',
            'preview' => 'from-pink-200 to-purple-200'
        ],
        'corporate' => [
            'name' => 'Corporate',
            'description' => 'Professional blue',
            'preview' => 'from-blue-600 to-blue-800'
        ],
        'synthwave' => [
            'name' => 'Synthwave',
            'description' => 'Retro futuristic',
            'preview' => 'from-purple-600 to-pink-600'
        ],
        'retro' => [
            'name' => 'Retro',
            'description' => 'Warm and vintage',
            'preview' => 'from-yellow-400 to-orange-400'
        ],
        'cyberpunk' => [
            'name' => 'Cyberpunk',
            'description' => 'Neon and bold',
            'preview' => 'from-yellow-400 to-pink-500'
        ],
        'valentine' => [
            'name' => 'Valentine',
            'description' => 'Romantic pink',
            'preview' => 'from-pink-300 to-red-300'
        ],
        'halloween' => [
            'name' => 'Halloween',
            'description' => 'Spooky orange',
            'preview' => 'from-orange-500 to-purple-600'
        ],
        'garden' => [
            'name' => 'Garden',
            'description' => 'Natural green',
            'preview' => 'from-green-400 to-green-600'
        ],
        'forest' => [
            'name' => 'Forest',
            'description' => 'Deep green',
            'preview' => 'from-green-700 to-green-900'
        ],
        'luxury' => [
            'name' => 'Luxury',
            'description' => 'Gold and black',
            'preview' => 'from-yellow-600 to-gray-800'
        ]
    ];

    public function mount()
    {
        // Initialize theme from user preferences or default
        $this->currentTheme = auth()->user()->theme_preference ?? 'light';
        $this->autoTheme = auth()->user()->auto_theme ?? false;
    }

    public function setTheme(string $theme)
    {
        $this->currentTheme = $theme;
        $this->autoTheme = false;

        // Save to user preferences
        auth()->user()->update([
            'theme_preference' => $theme,
            'auto_theme' => false
        ]);

        $this->dispatch('theme-changed', theme: $theme);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Theme changed to {$this->themes[$theme]['name']}"
        ]);
    }

    public function toggleAutoTheme()
    {
        $this->autoTheme = !$this->autoTheme;

        if ($this->autoTheme) {
            $this->currentTheme = 'auto';
        }

        // Save to user preferences
        auth()->user()->update([
            'theme_preference' => $this->autoTheme ? 'auto' : $this->currentTheme,
            'auto_theme' => $this->autoTheme
        ]);

        $this->dispatch('auto-theme-toggled', auto: $this->autoTheme);

        $message = $this->autoTheme ? 'Auto theme enabled' : 'Auto theme disabled';
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => $message
        ]);
    }

    public function render()
    {
        return view('livewire.components.theme-switcher');
    }
}
