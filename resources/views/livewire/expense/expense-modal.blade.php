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

            {{-- File uploads --}}
            <div>
    <p class="text-sm mb-1">File upload</p>

    <div
        x-data="{
            isOver: false,
            isUploading: false,
            progress: 0,
            pick() { $refs.files.click(); }
        }"
        x-on:dragover.prevent="isOver=true"
        x-on:dragleave.prevent="isOver=false"
        x-on:drop.prevent="isOver=false"
        x-on:livewire-upload-start="isUploading=true"
        x-on:livewire-upload-finish="isUploading=false;progress=0"
        x-on:livewire-upload-error="isUploading=false"
        x-on:livewire-upload-progress="progress=$event.detail.progress"
        class="flex flex-wrap gap-4"
    >
        {{-- Hidden input (bind to newFiles so picks append, not replace) --}}
        <input
            multiple
            x-ref="files"
            type="file"
            accept=".jpg,.jpeg,.png,.webp,.svg,.pdf,.xls,.xlsx,.csv"
            class="hidden"
            wire:model.live="newFiles"
            wire:key="file-input-{{ $expenseId ?? 'new' }}"
            x-on:change="$nextTick(()=>{$refs.files.value=null})"
            @disabled($isView)  {{-- Disable in view mode --}}
        />

        {{-- Staged NEW uploads (not yet saved to disk) --}}
        @foreach ($files as $i => $f)
            @php
                $ext    = strtolower($f->getClientOriginalExtension());
                $isImg  = in_array($ext, ['jpg','jpeg','png','webp','svg']);
                $name   = $f->getClientOriginalName();
                $sizeKb = number_format($f->getSize() / 1024, 0);
            @endphp

            <div class="w-[130px]">
                <div class="relative h-[100px] w-full rounded-xl border overflow-hidden group bg-slate-100">
                    @if ($isImg)
                        <img src="{{ $f->temporaryUrl() }}" class="object-contain w-full h-full" alt="preview">
                    @else
                        <div class="flex flex-col items-center justify-center w-full h-full text-xs p-2">
                            <span class="inline-grid place-items-center w-10 h-10 rounded-lg bg-slate-200 text-slate-700 font-semibold uppercase">{{ $ext }}</span>
                            <span class="mt-1 text-[10px] text-slate-500">{{ $sizeKb }} KB</span>
                        </div>
                    @endif

                    {{-- Only allow removing staged files in edit/create --}}
                    @unless($isView)
                        <button type="button"
                                wire:click="clearFile({{ $i }})"
                                class="absolute inset-0 hidden group-hover:flex items-center justify-center bg-black/40 text-white"
                                title="Remove">🗑️</button>
                    @endunless
                </div>

                <div class="mt-1 text-xs text-slate-700 truncate" title="{{ $name }}">{{ $name }}</div>
            </div>
        @endforeach

        {{-- EXISTING saved files (public disk) --}}
        @foreach ($existingFiles as $i => $p)
            @php
                $ext   = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                $isImg = in_array($ext, ['jpg','jpeg','png','webp','svg']);
                $name  = basename($p);
                $url   = asset('storage/'.$p);
            @endphp

            <div class="w-[130px]">
                <div class="relative h-[100px] w-full rounded-xl border overflow-hidden group bg-slate-100">
                    @if ($isImg)
                        <a href="{{ $url }}" target="_blank">
                            <img src="{{ $url }}" class="object-contain w-full h-full" alt="{{ $name }}">
                        </a>
                    @else
                        <a href="{{ $url }}" target="_blank" class="flex flex-col items-center justify-center w-full h-full text-xs p-2">
                            <span class="inline-grid place-items-center w-10 h-10 rounded-lg bg-slate-200 text-slate-700 font-semibold uppercase">{{ $ext }}</span>
                            <span class="mt-1 text-[10px] text-slate-500">Open</span>
                        </a>
                    @endif

                    {{-- Overlay action:
                         - View mode => Download
                         - Edit/Create => Delete --}}
                    @if ($isView)
                        <a href="{{ route('files.download', ['path' => $p]) }}"
                           class="absolute inset-0 hidden group-hover:flex items-center justify-center bg-black/40 text-white"
                           title="Download">⬇️</a>
                    @else
                        <button type="button"
                                wire:click="clearExistingFile({{ $i }})"
                                class="absolute inset-0 hidden group-hover:flex items-center justify-center bg-black/40 text-white"
                                title="Remove">🗑️</button>
                    @endif
                </div>

                <div class="mt-1 text-xs text-slate-700 truncate" title="{{ $name }}">{{ $name }}</div>
            </div>
        @endforeach

        {{-- Upload progress tile --}}
        <template x-if="isUploading">
            <div class="relative h-[100px] w-[130px] rounded-xl border overflow-hidden bg-slate-800 text-white grid place-items-center">
                <div class="text-xs">Uploading…</div>
                <div class="absolute bottom-0 left-0 h-1 bg-white/80" :style="`width:${progress}%`"></div>
            </div>
        </template>

        {{-- Add button (hidden in view mode) --}}
        @unless($isView)
            <button type="button"
                    @click="pick()"
                    class="h-[100px] w-[130px] rounded-xl border-2 border-dashed grid place-items-center hover:border-slate-900"
                    :class="isOver ? 'border-slate-900 bg-slate-50' : 'border-slate-300'"
                    title="Add file(s)"
                    x-show="!isUploading">
                <span class="text-2xl leading-none">+</span>
            </button>
        @endunless
    </div>

    @error('newFiles.*')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
    <p class="text-xs text-slate-500">jpg/jpeg/png/webp/svg, pdf, xls/xlsx/csv • up to 5MB each</p>
</div>


            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                {{-- Emoji Icon Picker --}}
                <div x-data="{ open: false, icon: @entangle('icon') }" class="relative col-span-1">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-2">
                        Icon
                    </label>

                    <!-- Preview -->
                    <div @click="open = !open"
                        class="cursor-pointer w-12 h-[38px] flex items-center justify-center border rounded-lg bg-white
               dark:bg-neutral-800 !border-neutral-300 dark:!border-neutral-500 text-lg select-none transition hover:bg-neutral-50">
                        <template x-if="!icon">
                            <span class="text-neutral-400 text-sm">Icon</span> Choose an icon
                        </template>
                        <template x-if="icon">
                            <span x-text="icon"></span>
                        </template>
                    </div>

                    <!-- Emoji Picker -->
                    <div x-show="open" @click.outside="open = false"
                        class="absolute z-50 bg-white border rounded-lg shadow-lg mt-2 w-auto overflow-hidden"
                        x-transition>
                        <emoji-picker @emoji-click="icon = $event.detail.unicode; open = false">
                        </emoji-picker>
                    </div>
                </div>


                {{-- Source --}}
                <div class="form-group col-span-4">
                    <x-input label="Expense Source *" wire:model.live="source"
                        class="rounded-lg !bg-white/10 !py-[9px] {{ $errors->has('source') ? '!border-red-500 focus:!ring-red-500' : '!border-neutral-300 dark:!border-neutral-500 focus:!ring-red-400' }}"
                        placeholder="eg. Chicken Fry" />
                </div>
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
                        <span wire:loading
                            wire:target="submit">{{ $expenseId ? 'Updating...' : 'Creating...' }}</span>
                    </flux:button>
                @endif
            </div>
        </form>
    </flux:modal>
