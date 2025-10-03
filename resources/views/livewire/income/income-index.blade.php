<div>
    {{-- Page Heading --}}
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" class="mb-4 flex items-center gap-2" level="1"><img class="w-8"
                src="{{ asset('assets/images/icons/wallet.png') }}" alt="Category Icon">{{ __('Income Sources') }}
        </flux:heading>
        <flux:separator variant="subtle" />
    </div>

    {{-- Create modal Button --}}
    <flux:modal.trigger name="income-modal">
        <flux:button class="cursor-pointer" icon="plus-circle" variant="primary"
            wire:click="$dispatch('open-income-modal', {mode: 'create'})">Add Income</flux:button>
    </flux:modal.trigger>

    {{-- Income Modal --}}
    <livewire:income.income-modal />

    {{-- Delete Confirmation Modal --}}
    <livewire:common.delete-confirmation />

    <!-- Table responsive wrapper -->
    <div class="border dark:border-none bg-white dark:bg-neutral-700 mt-8 p-4 sm:p-6 rounded-2xl">

        <!-- Top controls -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4">
            <div class="flex items-center flex-col md:flex-row gap-3">
                <!-- Search -->
                <div class="relative w-full sm:w-64">
                    <label for="inputSearch" class="sr-only">Search</label>
                    <input id="inputSearch" type="text" placeholder="Search..."
                        wire:model.live.debounce.300ms='search'
                        class="block w-full rounded-lg border dark:border-none dark:bg-neutral-600 py-2.5 pl-10 pr-4 text-sm focus:border-violet-400 focus:outline-none focus:ring-1 focus:ring-violet-400" />
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 transform">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-4 w-4 text-neutral-500 dark:text-neutral-200">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                </div>

                <!-- Filter -->
                <div class="relative w-full sm:w-40">
                    <label for="inputFilter" class="sr-only">Filter</label>
                    <select id="inputFilter" wire:model.live="range"
                        class="block w-full rounded-lg border dark:border-none dark:bg-neutral-600 p-2.5 text-sm focus:border-violet-400 focus:outline-none focus:ring-1 focus:ring-violet-400">
                        <option value="" selected>Default</option>
                        <option value="last_week">Last week</option>
                        <option value="last_month">Last month</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last_7_days">Last 7 days</option>
                        <option value="last_30_days">Last 30 days</option>
                    </select>
                </div>
            </div>

            <!-- Per Page -->
            <div class="flex items-center gap-2">
                <label for="inputFilter" class="text-neutral-600 dark:text-neutral-300">Per Page: </label>
                <select id="inputFilter" wire:model.live='perPage'
                    class="block rounded-lg border dark:border-none dark:bg-neutral-600 p-2.5 text-sm focus:border-violet-400 focus:outline-none focus:ring-1 focus:ring-violet-400 w-20">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <!-- Mobile list (xs only) -->
        <ul class="sm:hidden space-y-3">
            @forelse ($incomes as $income)
                <li class="rounded-xl border dark:border-neutral-600 p-3 bg-white dark:bg-neutral-700">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0 bg-accent rounded-lg p-2">
                            @if ($income->icon)
                                <x-dynamic-component :component="'heroicon-' . ($income->iconStyle ?? 'o') . '-' . $income->icon" class="w-5 h-5" />
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-medium truncate">{{ $income->source }}</p>
                                <flux:badge variant="solid" size="sm"
                                    color="green">
                                    {{ date('M d, Y', strtotime($income->income_date)) }}
                                </flux:badge>
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-2">

                                <span class="text-xs text-neutral-500 dark:text-neutral-300">
                                    <span class="font-medium text-lg pe-1">&#x09F3;</span>{{ $income->amount }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-3 flex items-center gap-2">
                        <flux:modal.trigger name="income-modal">
                            <flux:button
                                wire:click="$dispatch('open-income-modal', {mode: 'view', income: {{ $income }}})"
                                class="cursor-pointer h-[30px]" variant="primary" color="yellow">
                                view
                            </flux:button>

                            <flux:button
                                wire:click="$dispatch('open-income-modal', {mode: 'edit', income: {{ $income }}})"
                                class="cursor-pointer  h-[30px]" variant="primary" color="blue">
                                Edit
                            </flux:button>
                        </flux:modal.trigger>

                        <flux:modal.trigger name="delete-confirmation-modal">
                            <flux:button
                                wire:click="$dispatch('confirm-delete', {
                                    id: {{ $income->id }},
                                    dispatchAction: 'delete-income',
                                    modalName: 'delete-confirmation-modal',
                                    heading: 'Delete income?',
                                    message: 'You are about to delete this income. This action cannot be reversed.',
                                })"
                                class="cursor-pointer h-[30px]" variant="primary" color="red">
                                Delete
                            </flux:button>
                        </flux:modal.trigger>
                    </div>
                </li>
            @empty
                <li class="text-center py-4">No incomes found.</li>
            @endforelse
        </ul>

        <!-- Desktop table (≥sm) -->
        <div class="overflow-x-auto max-h-[50vh] mt-2 hidden sm:block">
            <table class="min-w-full text-left text-sm whitespace-nowrap">
                <thead
                    class="tracking-wider sticky top-0 bg-white dark:bg-neutral-700 outline-2 outline-neutral-200 dark:outline-neutral-600">
                    <tr>
                        <th scope="col" class="px-4 lg:px-6 py-3">#</th>
                        @include('livewire.common.sortable-th', [
                            'name' => 'source',
                            'displayName' => 'Income Source',
                        ])
                        @include('livewire.common.sortable-th', [
                            'name' => 'amount',
                            'displayName' => 'Amount',
                        ])
                        <th scope="col" class="px-4 lg:px-6 py-3">Note</th>
                        @include('livewire.common.sortable-th', [
                            'name' => 'income_date',
                            'displayName' => 'Date',
                        ])
                        <th scope="col" class="px-4 lg:px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incomes as $income)
                        <tr wire:key="{{ $income->id }}" class="border-b dark:border-neutral-600">
                            <th scope="row" class="px-4 lg:px-6 py-3">
                                {{ ($incomes->currentPage() - 1) * $incomes->perPage() + $loop->iteration }}
                            </th>
                            <td class="px-4 lg:px-6 py-3">{{-- Example: show category icon + name --}}
                                <div class="flex items-center gap-2">
                                    <div class="bg-accent-content p-2 rounded-lg shrink-0">
                                        @if ($income->icon)
                                            <x-dynamic-component :component="'heroicon-' . ($income->iconStyle ?? 'o') . '-' . $income->icon" class="w-5 h-5" />
                                        @endif
                                    </div>
                                    <span>{{ $income->source }}</span>
                                </div>

                            <td class="px-4 lg:px-6 py-3 capitalize">
                                <flux:badge icon="currency-bangladeshi" variant="solid"
                                    color="violet">
                                    {{ $income->amount }}
                                </flux:badge>
                            </td>
                            <td class="px-4 lg:px-6 py-3 capitalize truncate max-w-[16rem]">
                                {{ $income->note ?? '—' }}
                            </td>
                            <td class="px-4 lg:px-6 py-3 capitalize">
                                {{ date('M d, Y', strtotime($income->income_date)) }}
                            </td>

                            <td class="px-4 lg:px-6 py-3">
                                <div class="flex gap-2">
                                    <flux:modal.trigger name="income-modal">
                                        <flux:button
                                            wire:click="$dispatch('open-income-modal', {mode: 'view', income: {{ $income }}})"
                                            class="cursor-pointer min-h-[40px]" icon="eye" variant="primary"
                                            color="yellow">
                                        </flux:button>
                                        <flux:button
                                            wire:click="$dispatch('open-income-modal', {mode: 'edit', income: {{ $income }}})"
                                            class="cursor-pointer min-h-[40px]" icon="pencil" variant="primary"
                                            color="blue">
                                        </flux:button>
                                    </flux:modal.trigger>

                                    <flux:modal.trigger name="delete-confirmation-modal">
                                        <flux:button
                                            wire:click="$dispatch('confirm-delete', {
                                                id: {{ $income->id }},
                                                dispatchAction: 'delete-income',
                                                modalName: 'delete-confirmation-modal',
                                                heading: 'Delete income?',
                                                message: 'You are about to delete this income. This action cannot be undone.',
                                                })"
                                            class="cursor-pointer min-h-[40px]" icon="trash" variant="primary"
                                            color="red">
                                        </flux:button>
                                    </flux:modal.trigger>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 lg:px-6 pt-4 text-center">No incomes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav class="mt-4">
            <div class="sm:hidden text-center">
                {{ $incomes->onEachSide(0)->links() }}
            </div>
            <div class="hidden sm:block">
                {{ $incomes->links() }}
            </div>
        </nav>
    </div>

</div>
