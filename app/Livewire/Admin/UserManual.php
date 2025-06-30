<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class UserManual extends Component
{
    public $activeSection = 'overview';
    public $searchTerm = '';

    public $sections = [
        'overview' => [
            'title' => 'System Overview',
            'icon' => 'o-home',
            'content' => 'overview'
        ],
        'getting-started' => [
            'title' => 'Getting Started',
            'icon' => 'o-play',
            'content' => 'getting-started'
        ],
        'inventory' => [
            'title' => 'Inventory Management',
            'icon' => 'o-cube',
            'content' => 'inventory'
        ],
        'sales' => [
            'title' => 'Sales & POS',
            'icon' => 'o-shopping-cart',
            'content' => 'sales'
        ],
        'purchasing' => [
            'title' => 'Purchasing',
            'icon' => 'o-truck',
            'content' => 'purchasing'
        ],
        'reports' => [
            'title' => 'Reports',
            'icon' => 'o-chart-pie',
            'content' => 'reports'
        ],
        'admin' => [
            'title' => 'Administration',
            'icon' => 'o-cog-6-tooth',
            'content' => 'admin'
        ],
        'troubleshooting' => [
            'title' => 'Troubleshooting',
            'icon' => 'o-wrench-screwdriver',
            'content' => 'troubleshooting'
        ]
    ];

    public function render()
    {
        $filteredSections = $this->searchTerm ?
            array_filter($this->sections, function ($section) {
                return stripos($section['title'], $this->searchTerm) !== false;
            }) :
            $this->sections;

        return view('livewire.admin.user-manual', [
            'sections' => $this->sections,
            'filteredSections' => $filteredSections
        ])->layout('layouts.app', ['title' => 'User Manual']);
    }

    public function setActiveSection($section)
    {
        $this->activeSection = $section;
    }
}
