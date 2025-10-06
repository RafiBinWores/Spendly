<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl" class="flex items-center gap-2">
            <img class="w-8" src="{{ asset('assets/images/icons/report.png') }}" alt="Report Icon">
            {{ __('Transactions Report') }}
        </flux:heading>

        {{-- Export buttons --}}
        <div class="flex items-center gap-2">
            <a href="{{ $this->exportUrls['excel'] }}" target="_blank"
               class="px-3 py-2 rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700">
                Export Excel
            </a>
            <a href="{{ $this->exportUrls['pdf'] }}" target="_blank"
               class="px-3 py-2 rounded-lg border bg-white dark:bg-neutral-900 dark:border-neutral-700">
                Export PDF
            </a>
        </div>
    </div>

    {{-- Filters --}}
    {{-- Filters --}}
<div class="grid grid-cols-1 md:grid-cols-6 gap-3">
    <div>
        <label class="text-sm text-neutral-500">Type</label>
        <select wire:model.live="type"
                class="w-full border rounded-lg ps-3 pe-10 py-2 dark:bg-neutral-900 dark:border-neutral-700">
            <option value="all">All</option>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
        </select>
    </div>

    <div>
        <label class="text-sm text-neutral-500">From</label>
        <input type="date" wire:model.live="from"
               class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-900 dark:border-neutral-700">
    </div>

    <div>
        <label class="text-sm text-neutral-500">To</label>
        <input type="date" wire:model.live="to"
               class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-900 dark:border-neutral-700">
    </div>

    {{-- NEW: Category filter --}}
    <div>
        <label class="text-sm text-neutral-500">Category</label>
        <select wire:model.live="categoryId"
                class="w-full border rounded-lg ps-3 pe-10 py-2 dark:bg-neutral-900 dark:border-neutral-700"
                wire:key="report-cat-select">
            <option value="">All Categories</option>
            @foreach ($categoryOptions as $cat)
                <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
            @endforeach
        </select>
    </div>

    {{-- NEW: Sub-category filter (depends on Category) --}}
    <div>
        <label class="text-sm text-neutral-500">Sub-category</label>
        <select wire:model.live="subcategoryId"
                class="w-full border rounded-lg ps-3 pe-10 py-2 dark:bg-neutral-900 dark:border-neutral-700"
                {{ empty($subcategories) ? 'disabled' : '' }}
                wire:key="report-subcat-select-{{ $categoryId }}">
            <option value="">
                {{ empty($subcategories) ? 'Select Category first' : 'All Sub-categories' }}
            </option>
            @foreach ($subcategories as $sc)
                <option value="{{ $sc['id'] }}">{{ $sc['name'] }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-sm text-neutral-500">Show</label>
        <select wire:model.live="perPage"
                class="w-full border rounded-lg ps-3 pe-10 py-2 dark:bg-neutral-900 dark:border-neutral-700">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
    </div>
</div>


    {{-- Totals --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl border bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm">
            <div class="text-sm text-neutral-500">Total Income</div>
            <div class="mt-1 text-2xl font-semibold text-emerald-600 dark:text-emerald-400">
                ৳ {{ number_format($totals['income'], 2) }}
            </div>
        </div>
        <div class="p-4 rounded-2xl border bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm">
            <div class="text-sm text-neutral-500">Total Expense</div>
            <div class="mt-1 text-2xl font-semibold text-rose-600 dark:text-rose-400">
                ৳ {{ number_format($totals['expense'], 2) }}
            </div>
        </div>
        <div class="p-4 rounded-2xl border bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm">
            <div class="text-sm text-neutral-500">Balance</div>
            <div class="mt-1 text-2xl font-semibold {{ $totals['balance'] >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400' }}">
                ৳ {{ number_format($totals['balance'], 2) }}
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="p-4 rounded-2xl border bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-neutral-500">
                    <tr class="text-left">
                        <th class="py-2">Date</th>
                        <th class="py-2">Type</th>
                        <th class="py-2">Category</th>
                        <th class="py-2">Sub-category</th>
                        <th class="py-2">Note</th>
                        <th class="py-2 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-neutral-800">
                    @forelse ($rows as $r)
                        <tr>
                            <td class="py-2">{{ \Carbon\Carbon::parse($r->transacted_at)->format('d M Y, h:i A') }}</td>
                            <td class="py-2">
                                @if ($r->type === 'income')
                                    <span class="text-emerald-600 dark:text-emerald-400 font-medium">Income</span>
                                @else
                                    <span class="text-rose-600 dark:text-rose-400 font-medium">Expense</span>
                                @endif
                            </td>
                            <td class="py-2">{{ $r->category_name ?? '—' }}</td>
                            <td class="py-2">{{ $r->subcategory_name ?? '—' }}</td>
                            <td class="py-2 truncate max-w-[16rem]" title="{{ $r->note }}">{{ $r->note }}</td>
                            <td class="py-2 text-right font-medium">
                                @if ($r->type === 'income')
                                    <span class="text-emerald-600 dark:text-emerald-400">+৳ {{ number_format($r->amount, 2) }}</span>
                                @else
                                    <span class="text-rose-600 dark:text-rose-400">-৳ {{ number_format($r->amount, 2) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-neutral-500">No data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $rows->onEachSide(1)->links() }}
        </div>
    </div>
</div>
