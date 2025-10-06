    <flux:modal name="expense-modal" class="md:w-[32rem]">
        <form wire:submit="submit" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $isView ? 'Expense details' : ($expenseId ? 'Update' : 'Create') . ' Expense' }}
                </flux:heading>
                <flux:text class="mt-2">
                    {{ $isView ? 'View expense details information' : ($expenseId ? 'Update' : 'Add') . '  expense details information.' }}
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


            {{-- Source --}}
            <div class="form-group">
                <x-input label="Expense Source *" wire:model.live="source"
                    class="rounded-lg !bg-white/10 !py-[9px] {{ $errors->has('source') ? '!border-red-500 focus:!ring-red-500' : '!border-neutral-300 dark:!border-neutral-500 focus:!ring-red-400' }}"
                    placeholder="eg. Chicken Fry" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- amount --}}
                <div class="form-group">
                    <x-input label="Expense Amount *" wire:model.live="amount" type="number" step="1"
                        class="rounded-lg !bg-white/10 !py-[9px] {{ $errors->has('amount') ? '!border-red-500 focus:!ring-red-500' : '!border-neutral-300 dark:!border-neutral-500 focus:!ring-red-400' }}"
                        placeholder="eg. 50000" />
                </div>
                {{-- date --}}
                <div class="form-group">
                    <x-input label="Expense date *" wire:model.live="expense_date" type="date"
                        class="rounded-lg !bg-white/10 !py-[9px] {{ $errors->has('expense_date') ? '!border-red-500 focus:!ring-red-500' : '!border-neutral-300 dark:!border-neutral-500 focus:!ring-red-400' }}" />
                </div>
            </div>


            <div class="form-group">

                @php
                    $categories = App\Models\Category::where('status', 'active')->pluck('name', 'id');
                @endphp

                <x-select wire:model.live="category_id" label="Category" :options="$categories"
                    class="rounded-lg !bg-white/10 !py-[9px] {{ $errors->has('category_id') ? '!border-red-500 focus:!ring-red-500' : '!border-neutral-300 dark:!border-neutral-500 focus:!ring-red-500' }}"
                    clearable searchable />


            </div>

            {{-- Note --}}
            <div class="from-group md:col-span-2">
                <x-textarea label="Note" rows="3" wire:model.live="note"
                    class="ounded-lg !bg-white/10 !py-[9px] {{ $errors->has('note') ? '!border-red-500 focus:!ring-red-500' : '!border-neutral-300 dark:!border-neutral-500 focus:!ring-red-400' }}"
                    placeholder="Type.." />
            </div>

            {{-- Submit & Cancel button --}}
            <div class="flex">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button icon="x-circle" class="cursor-pointer me-2">Cancel</flux:button>
                </flux:modal.close>

                @if (!$isView)
                    <flux:button type="submit" icon="document-plus" class="cursor-pointer" variant="primary"
                        wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit">{{ $expenseId ? 'Update' : 'Create' }}</span>
                        <span wire:loading wire:target="submit">{{ $expenseId ? 'Updating...' : 'Creating...' }}</span>
                    </flux:button>
                @endif
            </div>
        </form>
    </flux:modal>
