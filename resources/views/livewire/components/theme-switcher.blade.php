<div class="p-4 w-80">
    <div class="flex items-center justify-between mb-4">
        <div class="text-sm font-semibold">Choose Theme</div>
        <x-mary-badge value="{{ count($themes) }} themes" class="badge-neutral badge-sm" />
    </div>

    {{-- Current Theme Display --}}
    <div class="p-3 mb-4 border rounded-lg bg-base-200">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm font-medium">
                    {{ $autoTheme ? 'Auto (System)' : $themes[$currentTheme]['name'] }}
                </div>
                <div class="text-xs text-gray-500">
                    {{ $autoTheme ? 'Follows system preference' : $themes[$currentTheme]['description'] }}
                </div>
            </div>
            @if (!$autoTheme)
                <div class="w-8 h-8 bg-gradient-to-r {{ $themes[$currentTheme]['preview'] }} rounded border"></div>
            @endif
        </div>
    </div>

    {{-- Auto Theme Toggle --}}
    <div class="p-3 mb-4 border rounded-lg">
        <label class="flex items-center justify-between cursor-pointer">
            <div>
                <div class="text-sm font-medium">Auto Theme</div>
                <div class="text-xs text-gray-500">Follow system preference</div>
            </div>
            <x-mary-toggle wire:model.live="autoTheme" wire:click="toggleAutoTheme" />
        </label>
    </div>

    {{-- Theme Grid --}}
    @if (!$autoTheme)
        <div class="grid grid-cols-2 gap-3 overflow-y-auto max-h-80">
            @foreach ($themes as $themeKey => $theme)
                <button wire:click="setTheme('{{ $themeKey }}')"
                    class="group p-3 rounded-lg border transition-all duration-200 hover:border-primary hover:shadow-md
                           {{ $currentTheme === $themeKey ? 'border-primary bg-primary/5' : 'border-base-300' }}">

                    {{-- Theme Preview --}}
                    <div
                        class="w-full h-8 bg-gradient-to-r {{ $theme['preview'] }} rounded mb-2 border border-black/10">
                    </div>

                    {{-- Theme Info --}}
                    <div class="text-center">
                        <div
                            class="text-xs font-medium group-hover:text-primary transition-colors
                                    {{ $currentTheme === $themeKey ? 'text-primary' : '' }}">
                            {{ $theme['name'] }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">{{ $theme['description'] }}</div>
                    </div>

                    {{-- Active Indicator --}}
                    @if ($currentTheme === $themeKey)
                        <div class="flex items-center justify-center mt-2">
                            <x-mary-icon name="o-check-circle" class="w-4 h-4 text-primary" />
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
    @endif

    {{-- Popular Themes Quick Access --}}
    @if (!$autoTheme)
        <div class="pt-3 mt-4 border-t">
            <div class="mb-2 text-xs font-medium text-gray-600">Quick Access</div>
            <div class="flex gap-2">
                <button wire:click="setTheme('light')"
                    class="flex-1 p-2 rounded text-xs border hover:border-primary transition-colors
                               {{ $currentTheme === 'light' ? 'border-primary bg-primary/5' : '' }}">
                    ☀️ Light
                </button>
                <button wire:click="setTheme('dark')"
                    class="flex-1 p-2 rounded text-xs border hover:border-primary transition-colors
                               {{ $currentTheme === 'dark' ? 'border-primary bg-primary/5' : '' }}">
                    🌙 Dark
                </button>
                <button wire:click="setTheme('corporate')"
                    class="flex-1 p-2 rounded text-xs border hover:border-primary transition-colors
                               {{ $currentTheme === 'corporate' ? 'border-primary bg-primary/5' : '' }}">
                    💼 Corporate
                </button>
            </div>
        </div>
    @endif

    {{-- Theme Info --}}
    <div class="pt-3 mt-4 border-t">
        <div class="text-xs text-center text-gray-500">
            Theme preferences are saved to your account
        </div>
    </div>
</div>

{{-- JavaScript for theme switching --}}
<script>
    document.addEventListener('livewire:init', () => {
        // Listen for theme changes
        Livewire.on('theme-changed', (event) => {
            document.documentElement.setAttribute('data-theme', event.theme);
            localStorage.setItem('theme', event.theme);
            localStorage.removeItem('theme-auto');
        });

        // Listen for auto theme toggle
        Livewire.on('auto-theme-toggled', (event) => {
            if (event.auto) {
                // Follow system preference
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = prefersDark ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('theme-auto', 'true');
                localStorage.removeItem('theme');
            } else {
                localStorage.removeItem('theme-auto');
            }
        });

        // Initialize theme on page load
        const savedTheme = localStorage.getItem('theme');
        const autoTheme = localStorage.getItem('theme-auto');

        if (autoTheme === 'true') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
        } else if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
        }

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem('theme-auto') === 'true') {
                document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            }
        });
    });
</script>
