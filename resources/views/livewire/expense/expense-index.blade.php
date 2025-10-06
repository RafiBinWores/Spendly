<div>
    {{-- Page Heading --}}
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" class="mb-4 flex items-center gap-2" level="1"><img class="w-8"
                src="{{ asset('assets/images/icons/expense.png') }}" alt="Category Icon">{{ __('Expense') }}
        </flux:heading>
        <flux:separator variant="subtle" />
    </div>

    {{-- Create modal Button --}}
    <flux:modal.trigger name="expense-modal">
        <flux:button class="cursor-pointer" icon="plus-circle" variant="primary"
            wire:click="$dispatch('open-expense-modal', {mode: 'create'})">Add Expense</flux:button>
    </flux:modal.trigger>

    {{-- Expense Modal --}}
    <livewire:expense.expense-modal />

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
            @forelse ($expenses as $expense)
                <li class="rounded-xl border dark:border-neutral-600 p-3 bg-white dark:bg-neutral-700">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0 bg-neutral-800 rounded-lg p-2">
                            @if ($expense->icon)
                                <span>{{ $expense->icon }}</span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-medium truncate">{{ $expense->source }}</p>
                                <flux:badge variant="solid" size="sm"
                                    color="green">
                                    {{ date('M d, Y', strtotime($expense->expense_date)) }}
                                </flux:badge>
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-2">

                                <span class="text-xs text-neutral-500 dark:text-neutral-300">
                                    <span class="font-medium text-lg pe-1">&#x09F3;</span>{{ $expense->amount }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-3 flex items-center gap-2">
                        <flux:modal.trigger name="expense-modal">
                            <flux:button
                                wire:click="$dispatch('open-expense-modal', {mode: 'view', expense: {{ $expense }}})"
                                class="cursor-pointer h-[30px]" variant="primary" color="yellow">
                                view
                            </flux:button>

                            <flux:button
                                wire:click="$dispatch('open-expense-modal', {mode: 'edit', expense: {{ $expense }}})"
                                class="cursor-pointer  h-[30px]" variant="primary" color="blue">
                                Edit
                            </flux:button>
                        </flux:modal.trigger>

                        <flux:modal.trigger name="delete-confirmation-modal">
                            <flux:button
                                wire:click="$dispatch('confirm-delete', {
                                    id: {{ $expense->id }},
                                    dispatchAction: 'delete-expense',
                                    modalName: 'delete-confirmation-modal',
                                    heading: 'Delete expense?',
                                    message: 'You are about to delete this expense. This action cannot be reversed.',
                                })"
                                class="cursor-pointer h-[30px]" variant="primary" color="red">
                                Delete
                            </flux:button>
                        </flux:modal.trigger>
                    </div>
                </li>
            @empty
                <li class="text-center py-4">No expenses found.</li>
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
                            'displayName' => 'Expense Source',
                        ])
                        @include('livewire.common.sortable-th', [
                            'name' => 'amount',
                            'displayName' => 'Amount',
                        ])
                        <th scope="col" class="px-4 lg:px-6 py-3">
                            Category
                        </th>
                        <th scope="col" class="px-4 lg:px-6 py-3">
                            Sub Category
                        </th>
                        @include('livewire.common.sortable-th', [
                            'name' => 'expense_date',
                            'displayName' => 'Date',
                        ])
                        <th scope="col" class="px-4 lg:px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr wire:key="{{ $expense->id }}" class="border-b dark:border-neutral-600">
                            <th scope="row" class="px-4 lg:px-6 py-3">
                                {{ ($expenses->currentPage() - 1) * $expenses->perPage() + $loop->iteration }}
                            </th>
                            <td class="px-4 lg:px-6 py-3">{{-- Example: show category icon + name --}}
                                <div class="flex items-center gap-2">
                                    <div class="bg-neutral-800 p-2 rounded-lg shrink-0">
                                        @if ($expense->icon)
                                            <span>{{ $expense->icon }}</span>
                                        @endif
                                    </div>
                                    <span>{{ $expense->source }}</span>
                                </div>

                            <td class="px-4 lg:px-6 py-3 capitalize">
                                <flux:badge icon="currency-bangladeshi" variant="solid"
                                    color="violet">
                                    {{ $expense->amount }}
                                </flux:badge>
                            </td>
                            <td class="px-4 lg:px-6 py-3 capitalize">
                                {{ $expense->category->name ?? 'Uncategorized' }}
                            </td>
                            <td class="px-4 lg:px-6 py-3 capitalize">
                                {{ $expense->subCategories->name ?? 'Uncategorized' }}
                            </td>
                            <td class="px-4 lg:px-6 py-3 capitalize">
                                {{ date('M d, Y', strtotime($expense->expense_date)) }}
                            </td>

                            <td class="px-4 lg:px-6 py-3">
                                <div class="flex gap-2">
                                    <flux:modal.trigger name="expense-modal">
                                        <flux:button
                                            wire:click="$dispatch('open-expense-modal', {mode: 'view', expense: {{ $expense }}})"
                                            class="cursor-pointer min-h-[40px]" icon="eye" variant="primary"
                                            color="yellow">
                                        </flux:button>
                                        <flux:button
                                            wire:click="$dispatch('open-expense-modal', {mode: 'edit', expense: {{ $expense }}})"
                                            class="cursor-pointer min-h-[40px]" icon="pencil" variant="primary"
                                            color="blue">
                                        </flux:button>
                                    </flux:modal.trigger>

                                    <flux:modal.trigger name="delete-confirmation-modal">
                                        <flux:button
                                            wire:click="$dispatch('confirm-delete', {
                                                id: {{ $expense->id }},
                                                dispatchAction: 'delete-expense',
                                                modalName: 'delete-confirmation-modal',
                                                heading: 'Delete expense?',
                                                message: 'You are about to delete this expense. This action cannot be undone.',
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
                            <td colspan="6" class="px-4 lg:px-6 pt-4 text-center">No expenses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav class="mt-4">
            <div class="sm:hidden text-center">
                {{ $expenses->onEachSide(0)->links() }}
            </div>
            <div class="hidden sm:block">
                {{ $expenses->links() }}
            </div>
        </nav>
    </div>
    
</div>
