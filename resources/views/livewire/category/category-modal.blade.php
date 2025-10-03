    <flux:modal name="category-modal" class="md:w-[32rem]">
        <form wire:submit="submit" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $isView ? 'Category details' : ($categoryId ? 'Update' : 'Create') . ' Category' }}
                </flux:heading>
                <flux:text class="mt-2">
                    {{ $isView ? 'View category details information' : ($categoryId ? 'Update' : 'Add') . '  category details information.' }}
                </flux:text>
            </div>


            {{-- Icon (Heroicons) --}}
<div class="form-group"
     x-data="{
        open: false,
        q: '',
        get list() { return @js($availableIcons); },
        get filtered() {
            const t = this.q.trim().toLowerCase();
            return t ? this.list.filter(i => i.toLowerCase().includes(t)) : this.list;
        }
     }">
    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
        Category Icon
    </label>

    {{-- Preview + Controls --}}
    <div class="flex items-center gap-3">
        <div class="relative w-10 h-10 flex items-center justify-center rounded-lg border border-neutral-200 bg-white
                    dark:bg-neutral-800 dark:border-neutral-700">
            @if ($icon)
                <x-dynamic-component :component="'heroicon-' . $iconStyle . '-' . $icon"
                    class="w-6 h-6 text-neutral-800 dark:text-neutral-200" />
            @else
                <span class="text-xs text-neutral-400 dark:text-neutral-500">Icon</span>
            @endif

            {{-- LOADING OVERLAY for icon/iconStyle change --}}
            <div wire:loading.delay
                 wire:target="icon,iconStyle"
                 class="absolute inset-0 flex items-center justify-center bg-white/60 dark:bg-neutral-900/40 rounded-lg"
                 aria-live="polite" aria-busy="true">
                <svg class="animate-spin w-5 h-5" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                    <path class="opacity-90" fill="currentColor"
                        d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z" />
                </svg>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{-- Open popover --}}
            <button type="button"
                class="cursor-pointer inline-flex items-center px-3 py-2 rounded-lg border
                       border-neutral-300 hover:border-neutral-400 text-sm bg-white
                       dark:bg-neutral-800 dark:text-neutral-200 dark:border-neutral-700 dark:hover:border-neutral-600
                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-500 dark:focus:ring-offset-neutral-900"
                @click="open = !open"
                :disabled="@js($isView)"
                wire:loading.attr="disabled"
                wire:target="icon,iconStyle">
                Choose Icon
            </button>

            {{-- Clear selected --}}
            @if ($icon && !$isView)
                <button type="button"
                    class="relative cursor-pointer inline-flex items-center gap-2 px-3 py-2 rounded-lg border
                           border-neutral-300 hover:border-neutral-400 text-sm bg-white
                           dark:bg-neutral-800 dark:text-neutral-200 dark:border-neutral-700 dark:hover:border-neutral-600
                           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-500 dark:focus:ring-offset-neutral-900"
                    wire:click="$set('icon','')"
                    wire:loading.attr="disabled"
                    wire:target="icon">
                    <span>Clear</span>
                    <svg wire:loading wire:target="icon" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z" />
                    </svg>
                </button>
            @endif

            {{-- Outline / Solid toggle --}}
            <div class="inline-flex rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden ml-2">
                <button type="button"
                    class="relative px-3 py-1 text-sm bg-white dark:bg-neutral-800 dark:text-neutral-200 cursor-pointer"
                    :class="@js($iconStyle) === 'o' ? 'bg-neutral-100 dark:bg-neutral-900 font-medium' : ''"
                    wire:click="$set('iconStyle','o')"
                    @disabled($isView)
                    wire:loading.attr="disabled"
                    wire:target="iconStyle">
                    Outline
                    <svg wire:loading wire:target="iconStyle" class="animate-spin w-4 h-4 inline-block ml-1 align-[-2px]" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z" />
                    </svg>
                </button>

                <button type="button"
                    class="relative px-3 py-1 text-sm bg-white dark:bg-neutral-800 dark:text-neutral-200 cursor-pointer"
                    :class="@js($iconStyle) === 's' ? 'bg-neutral-100 dark:bg-neutral-900 font-medium' : ''"
                    wire:click="$set('iconStyle','s')"
                    @disabled($isView)
                    wire:loading.attr="disabled"
                    wire:target="iconStyle">
                    Solid
                    <svg wire:loading wire:target="iconStyle" class="animate-spin w-4 h-4 inline-block ml-1 align-[-2px]" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Inline picker popover --}}
    <div x-cloak x-show="open" @click.outside="open = false" x-transition class="relative z-20 mt-3">
        <div class="absolute left-0 right-0 rounded-xl border border-neutral-200 dark:border-neutral-700 shadow-lg p-3
                    bg-white dark:bg-neutral-800">
            {{-- Search --}}
            <div class="mb-3">
                <input x-model="q" type="text" placeholder="Search icons…"
                    class="w-full rounded-lg border border-neutral-200 dark:border-neutral-700
                           bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100
                           px-3 py-2 focus:outline-none focus:ring-2 focus:ring-neutral-500" />
            </div>

            {{-- Grid --}}
            <div class="grid grid-cols-6 gap-3 max-h-[18rem] overflow-y-auto pr-1
                        scrollbar-thin scrollbar-thumb-neutral-300 dark:scrollbar-thumb-neutral-700">
                @foreach ($availableIcons as $name)
                    <button type="button"
                        wire:key="icon-{{ $name }}"
                        x-show="!q || '{{ $name }}'.toLowerCase().includes(q.toLowerCase())"
                        class="relative flex flex-col items-center gap-1 rounded-lg border p-2 transition
                               border-neutral-200 hover:border-neutral-400 bg-white
                               dark:bg-neutral-800 dark:border-neutral-700 dark:hover:border-neutral-600"
                        wire:click="$set('icon','{{ $name }}')"
                        wire:loading.attr="disabled"
                        wire:target="icon"
                        @disabled($isView)>
                        <x-dynamic-component :component="'heroicon-' . $iconStyle . '-' . $name"
                            class="w-6 h-6 text-neutral-800 dark:text-neutral-200" />
                        <span class="text-[10px] text-neutral-600 dark:text-neutral-300 truncate w-full text-center">
                            {{ $name }}
                        </span>

                        {{-- spinner overlay while selecting --}}
                        <div wire:loading wire:target="icon"
                            class="absolute inset-0 flex items-center justify-center rounded-lg bg-white/60 dark:bg-neutral-900/40">
                            <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z" />
                            </svg>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
        Pick from Heroicons.
    </p>
</div>





                {{-- Name --}}
                <div class="form-group">
                    <flux:input :disabled="$isView" wire:model="name" label="Name" placeholder="Category name" />
                </div>

                {{-- Status select --}}
                <div class="form-group">
                    <flux:select :disabled="$isView" wire:model="status" label="Status"
                        placeholder="Choose status...">
                        <flux:select.option value="active">Active</flux:select.option>
                        <flux:select.option value="disable">Disable</flux:select.option>
                    </flux:select>
                </div>

                {{-- Submit & Cancel button --}}
                <div class="flex">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button icon="x-circle" class="cursor-pointer me-2">Cancel</flux:button>
                    </flux:modal.close>

                    @if (!$isView)
                        <flux:button type="submit" icon="x-circle" class="cursor-pointer" variant="primary"
                            wire:loading.attr="disabled" wire:target="submit">
                            <span wire:loading.remove
                                wire:target="submit">{{ $categoryId ? 'Update' : 'Create' }}</span>
                            <span wire:loading
                                wire:target="submit">{{ $categoryId ? 'Updating...' : 'Creating...' }}</span>
                        </flux:button>
                    @endif
                </div>
        </form>
    </flux:modal>
