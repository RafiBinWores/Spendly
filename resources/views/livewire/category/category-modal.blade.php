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

            {{-- Emoji Icon Picker --}}
            <div x-data="{ open: false, icon: @entangle('icon') }" class="relative">
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                    Choose an Icon
                </label>

                <!-- Preview -->
                <div @click="open = !open"
                    class="cursor-pointer size-12 flex items-center justify-center border rounded-lg bg-white
               dark:bg-neutral-800 dark:border-neutral-700 text-lg select-none transition hover:bg-neutral-50">
                    <template x-if="!icon">
                        <span class="text-neutral-400 text-sm">Icon</span> Choose an icon
                    </template>
                    <template x-if="icon">
                        <span x-text="icon"></span>
                    </template>
                </div>

                <!-- Emoji Picker -->
                <div x-show="open" @click.outside="open = false"
                    class="absolute z-50 bg-white border rounded-lg shadow-lg mt-2 w-72 overflow-hidden" x-transition>
                    <emoji-picker @emoji-click="icon = $event.detail.unicode; open = false">
                    </emoji-picker>
                </div>
            </div>

            {{-- Name --}}
            <div class="form-group">
                <flux:input :disabled="$isView" wire:model="name" label="Name" placeholder="Category name" />
            </div>

            {{-- Status select --}}
            <div class="form-group">
                <flux:select :disabled="$isView" wire:model="status" label="Status" placeholder="Choose status...">
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
                        <span wire:loading.remove wire:target="submit">{{ $categoryId ? 'Update' : 'Create' }}</span>
                        <span wire:loading wire:target="submit">{{ $categoryId ? 'Updating...' : 'Creating...' }}</span>
                    </flux:button>
                @endif
            </div>
        </form>
    </flux:modal>
