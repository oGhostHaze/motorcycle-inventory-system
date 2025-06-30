<div class="min-h-screen bg-base-100">
    {{-- Header --}}
    <div class="border-b bg-base-200 border-base-300">
        <div class="px-4 py-4 mx-auto max-w-7xl sm:px-6 lg:px-8 lg:py-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold lg:text-3xl text-base-content">User Manual</h1>
                    <p class="mt-1 text-sm text-base-content/70 lg:text-base">Comprehensive guide to using the Motorcycle
                        Parts Inventory System</p>
                </div>

                {{-- Search --}}
                <div class="w-full lg:w-96">
                    <x-mary-input wire:model.live="searchTerm" placeholder="Search manual..." icon="o-magnifying-glass"
                        clearable />
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 py-4 mx-auto max-w-7xl sm:px-6 lg:px-8 lg:py-8">
        {{-- Mobile Section Selector --}}
        <div class="mb-6 lg:hidden">
            <div class="w-full dropdown dropdown-bottom">
                <div tabindex="0" role="button" class="justify-between w-full btn btn-outline">
                    <div class="flex items-center gap-2">
                        <x-mary-icon name="{{ $sections[$activeSection]['icon'] }}" class="w-5 h-5" />
                        <span>{{ $sections[$activeSection]['title'] }}</span>
                    </div>
                    <x-mary-icon name="o-chevron-down" class="w-4 h-4" />
                </div>
                <ul tabindex="0"
                    class="dropdown-content menu bg-base-200 rounded-box z-[1] w-full p-2 shadow-lg max-h-96 overflow-y-auto">
                    @foreach ($filteredSections as $key => $section)
                        <li>
                            <button wire:click="setActiveSection('{{ $key }}')"
                                class="flex items-center gap-3 {{ $activeSection === $key ? 'bg-primary text-primary-content' : '' }}"
                                onclick="document.activeElement.blur()">
                                <x-mary-icon name="{{ $section['icon'] }}" class="w-5 h-5" />
                                <span>{{ $section['title'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
            {{-- Desktop Sidebar Navigation --}}
            <div class="flex-shrink-0 hidden lg:block w-80">
                <div class="sticky top-8">
                    <div class="p-4 rounded-lg bg-base-200">
                        <h3 class="mb-4 text-lg font-semibold text-base-content">Contents</h3>
                        <nav class="space-y-2">
                            @foreach ($filteredSections as $key => $section)
                                <button wire:click="setActiveSection('{{ $key }}')"
                                    class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                                        {{ $activeSection === $key ? 'bg-primary text-primary-content' : 'hover:bg-base-300 text-base-content' }}">
                                    <x-mary-icon name="{{ $section['icon'] }}" class="w-5 h-5" />
                                    <span class="text-sm">{{ $section['title'] }}</span>
                                </button>
                            @endforeach
                        </nav>
                    </div>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="flex-1 min-w-0">
                <div class="p-4 rounded-lg bg-base-200 sm:p-6 lg:p-8">
                    @switch($activeSection)
                        @case('overview')
                            @include('livewire.admin.user-manual.sections.overview')
                        @break

                        @case('getting-started')
                            @include('livewire.admin.user-manual.sections.getting-started')
                        @break

                        @case('inventory')
                            @include('livewire.admin.user-manual.sections.inventory')
                        @break

                        @case('sales')
                            @include('livewire.admin.user-manual.sections.sales')
                        @break

                        @case('purchasing')
                            @include('livewire.admin.user-manual.sections.purchasing')
                        @break

                        @case('reports')
                            @include('livewire.admin.user-manual.sections.reports')
                        @break

                        @case('admin')
                            @include('livewire.admin.user-manual.sections.admin')
                        @break

                        @case('troubleshooting')
                            @include('livewire.admin.user-manual.sections.troubleshooting')
                        @break

                        @default
                            @include('livewire.admin.user-manual.sections.overview')
                    @endswitch
                </div>

                {{-- Mobile Navigation Buttons --}}
                <div class="flex flex-col gap-3 mt-6 lg:hidden sm:flex-row">
                    @php
                        $sectionKeys = array_keys($sections);
                        $currentIndex = array_search($activeSection, $sectionKeys);
                        $prevSection = $currentIndex > 0 ? $sectionKeys[$currentIndex - 1] : null;
                        $nextSection = $currentIndex < count($sectionKeys) - 1 ? $sectionKeys[$currentIndex + 1] : null;
                    @endphp

                    @if ($prevSection)
                        <button wire:click="setActiveSection('{{ $prevSection }}')"
                            class="flex-1 btn btn-outline sm:flex-none">
                            <x-mary-icon name="o-chevron-left" class="w-4 h-4" />
                            <span class="hidden sm:inline">{{ $sections[$prevSection]['title'] }}</span>
                            <span class="sm:hidden">Previous</span>
                        </button>
                    @endif

                    @if ($nextSection)
                        <button wire:click="setActiveSection('{{ $nextSection }}')"
                            class="btn btn-primary flex-1 sm:flex-none {{ !$prevSection ? 'sm:ml-auto' : '' }}">
                            <span class="hidden sm:inline">{{ $sections[$nextSection]['title'] }}</span>
                            <span class="sm:hidden">Next</span>
                            <x-mary-icon name="o-chevron-right" class="w-4 h-4" />
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mobile Floating Action Button --}}
        <div class="fixed z-50 lg:hidden bottom-6 right-6">
            <div class="dropdown dropdown-top dropdown-end">
                <div tabindex="0" role="button" class="shadow-lg btn btn-circle btn-primary">
                    <x-mary-icon name="o-bars-3" class="w-6 h-6" />
                </div>
                <ul tabindex="0"
                    class="dropdown-content menu bg-base-200 rounded-box z-[1] w-72 p-2 shadow-xl max-h-80 overflow-y-auto mb-2">
                    <li class="menu-title">
                        <span>Jump to Section</span>
                    </li>
                    @foreach ($filteredSections as $key => $section)
                        <li>
                            <button wire:click="setActiveSection('{{ $key }}')"
                                class="flex items-center gap-3 {{ $activeSection === $key ? 'bg-primary text-primary-content' : '' }}"
                                onclick="document.activeElement.blur()">
                                <x-mary-icon name="{{ $section['icon'] }}" class="w-4 h-4" />
                                <span class="text-sm">{{ $section['title'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
