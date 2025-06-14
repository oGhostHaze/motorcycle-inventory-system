<div>
    {{-- Page Header --}}
    <x-mary-header title="Recompute Return Totals" subtitle="Fix incorrect return calculations and shift totals"
        separator />

    {{-- Warning Card --}}
    <x-mary-card class="mb-6 border-warning bg-warning/5">
        <div class="flex items-start gap-3">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6 mt-1 text-warning" />
            <div>
                <h3 class="font-semibold text-warning">Important Information</h3>
                <p class="mt-1 text-sm text-gray-700">
                    This tool recalculates return totals and shift amounts based on actual processed returns.
                    Always run with "Preview Changes" first to see what will be modified.
                </p>
            </div>
        </div>
    </x-mary-card>

    {{-- Recompute Options --}}
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        {{-- Sale Items --}}
        <x-mary-card>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-document-text class="w-8 h-8 text-info" />
                    <div>
                        <h3 class="font-semibold">Sale Item Returned Quantities</h3>
                        <p class="text-sm text-gray-600">Fix returned_quantity tracking on sale items</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <x-mary-button wire:click="runRecompute('items', true)" class="w-full btn-outline btn-info btn-sm"
                        wire:loading.attr="disabled" wire:target="runRecompute">
                        <x-heroicon-o-eye class="w-4 h-4" />
                        <span wire:loading.remove wire:target="runRecompute">Preview Changes</span>
                        <span wire:loading wire:target="runRecompute">Loading...</span>
                    </x-mary-button>

                    <x-mary-button wire:click="runRecompute('items', false)" class="w-full btn-info btn-sm"
                        wire:loading.attr="disabled" wire:target="runRecompute">
                        <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                        <span wire:loading.remove wire:target="runRecompute">Apply Changes</span>
                        <span wire:loading wire:target="runRecompute">Processing...</span>
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>

        {{-- Shift Totals --}}
        <x-mary-card>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-clock class="w-8 h-8 text-success" />
                    <div>
                        <h3 class="font-semibold">Shift Return Totals</h3>
                        <p class="text-sm text-gray-600">Fix return amounts in sales shifts</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <x-mary-button wire:click="runRecompute('shifts', true)"
                        class="w-full btn-outline btn-success btn-sm" wire:loading.attr="disabled"
                        wire:target="runRecompute">
                        <x-heroicon-o-eye class="w-4 h-4" />
                        <span wire:loading.remove wire:target="runRecompute">Preview Changes</span>
                        <span wire:loading wire:target="runRecompute">Loading...</span>
                    </x-mary-button>

                    <x-mary-button wire:click="runRecompute('shifts', false)" class="w-full btn-success btn-sm"
                        wire:loading.attr="disabled" wire:target="runRecompute">
                        <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                        <span wire:loading.remove wire:target="runRecompute">Apply Changes</span>
                        <span wire:loading wire:target="runRecompute">Processing...</span>
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>
    </div>

    {{-- Full Recompute --}}
    <x-mary-card class="mt-6">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <x-heroicon-o-arrow-path class="w-8 h-8 text-primary" />
                <div>
                    <h3 class="font-semibold">Complete Recomputation</h3>
                    <p class="text-sm text-gray-600">Recompute all return-related calculations</p>
                </div>
            </div>

            <div class="flex gap-2">
                <x-mary-button wire:click="runRecompute('all', true)" class="btn-outline btn-primary"
                    wire:loading.attr="disabled" wire:target="runRecompute">
                    <x-heroicon-o-eye class="w-4 h-4" />
                    <span wire:loading.remove wire:target="runRecompute">Preview All Changes</span>
                    <span wire:loading wire:target="runRecompute">Loading...</span>
                </x-mary-button>

                <x-mary-button wire:click="runRecompute('all', false)" class="btn-primary" wire:loading.attr="disabled"
                    wire:target="runRecompute">
                    <x-heroicon-o-cog-6-tooth class="w-4 h-4" />
                    <span wire:loading.remove wire:target="runRecompute">Apply All Changes</span>
                    <span wire:loading wire:target="runRecompute">Processing...</span>
                </x-mary-button>
            </div>
        </div>
    </x-mary-card>

    {{-- Output Display --}}
    @if ($output)
        <x-mary-card class="mt-6">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-command-line class="w-5 h-5" />
                        <h3 class="font-semibold">Command Output</h3>
                        @if ($lastCommand)
                            <x-mary-badge value="php artisan {{ $lastCommand }}" class="badge-ghost badge-sm" />
                        @endif
                    </div>
                    <x-mary-button wire:click="clearOutput" class="btn-ghost btn-sm">
                        Clear Output
                    </x-mary-button>
                </div>

                <div
                    class="p-4 overflow-y-auto font-mono text-sm text-green-400 whitespace-pre-wrap bg-gray-900 rounded-lg max-h-96">
                    {{ $output }}</div>
            </div>
        </x-mary-card>
    @endif
</div>
