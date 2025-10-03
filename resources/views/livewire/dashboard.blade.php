<div>
    {{-- Page Heading --}}
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" class="mb-4 flex items-center gap-2" level="1">
            <img class="w-8" src="{{ asset('assets/images/icons/dashboard.png') }}" alt="Category Icon">
            {{ __('Dashboard') }}
        </flux:heading>
        <flux:separator variant="subtle" />
    </div>

    <div class="space-y-6" wire:poll.30s>
        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <label class="text-sm text-neutral-500">Range</label>
                <select wire:model.live="range"
                        class="border rounded-lg ps-3 pe-10 py-2 dark:bg-neutral-900 dark:border-neutral-700">
                    <option value="today">Today</option>
                    <option value="this_week">This Week</option>
                    <option value="this_month">This Month</option>
                    <option value="this_year">This Year</option>
                    <option value="all">All Time</option>
                </select>
            </div>
            <div>
                <label class="text-sm text-neutral-500">Show</label>
                <select wire:model.live="perPage"
                        class="border rounded-lg ps-3 pe-10 py-2 dark:bg-neutral-900 dark:border-neutral-700">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                </select>
            </div>
            <div wire:loading.delay wire:target="range,perPage" class="text-sm text-neutral-500">
                Updating…
            </div>
        </div>

        {{-- KPI cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl border bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm">
                <div class="text-sm text-neutral-500">Total Income</div>
                <div class="mt-1 text-2xl font-semibold text-emerald-600 dark:text-emerald-400">
                    ৳ {{ number_format($this->totalIncome, 2) }}
                </div>
            </div>

            <div class="p-4 rounded-2xl border bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm">
                <div class="text-sm text-neutral-500">Total Expense</div>
                <div class="mt-1 text-2xl font-semibold text-rose-600 dark:text-rose-400">
                    ৳ {{ number_format($this->totalExpense, 2) }}
                </div>
            </div>

            <div class="p-4 rounded-2xl border bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm">
                <div class="text-sm text-neutral-500">Balance</div>
                <div class="mt-1 text-2xl font-semibold {{ $this->balance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400' }}">
                    ৳ {{ number_format($this->balance, 2) }}
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Pie Chart --}}
            <div class="p-4 rounded-2xl border bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm" wire:ignore>
                <h3 class="font-semibold mb-2">Overview (Pie)</h3>
                <div class="relative h-64">
                    <canvas id="expensePie" class="absolute inset-0 w-full h-full"></canvas>
                </div>
                <ul class="mt-4 space-y-2 text-sm">
                    <li class="flex justify-between">
                        <span>Income</span>
                        <span class="font-medium">৳ {{ number_format($chartDataPie['values'][0], 2) }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span>Expense</span>
                        <span class="font-medium">৳ {{ number_format($chartDataPie['values'][1], 2) }}</span>
                    </li>
                    <li class="flex justify-between">
                        <span>Balance</span>
                        <span class="font-medium">৳ {{ number_format($chartDataPie['values'][2], 2) }}</span>
                    </li>
                </ul>
            </div>

            {{-- Line Chart --}}
            <div class="p-4 rounded-2xl border bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm" wire:ignore>
                <h3 class="font-semibold mb-2">Income vs Expense (Line)</h3>
                <div class="relative h-64">
                    <canvas id="incomeExpenseLine" class="absolute inset-0 w-full h-full"></canvas>
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="p-4 rounded-2xl border bg-white dark:bg-neutral-900 dark:border-neutral-800 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold">Recent Transactions</h3>
                <div class="text-xs text-neutral-500">Last {{ $perPage }}</div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-neutral-500">
                        <tr class="text-left">
                            <th class="py-2">Date</th>
                            <th class="py-2">Type</th>
                            <th class="py-2">Category</th>
                            <th class="py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-neutral-800">
                        @forelse ($transactions as $t)
                            <tr>
                                <td class="py-2">{{ \Carbon\Carbon::parse($t->transacted_at)->format('d M Y, h:i A') }}</td>
                                <td class="py-2">
                                    <span class="inline-flex items-center gap-2">
                                        @if ($t->type === 'income')
                                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <a href="{{ route('incomes.index') }}" wire:navigate class="text-emerald-600 dark:text-emerald-400 font-medium">Income</a>
                                        @else
                                            <span class="inline-block w-2 h-2 rounded-full bg-rose-500"></span>
                                            <a href="{{ route('expenses.index') }}" wire:navigate class="text-rose-600 dark:text-rose-400 font-medium">Expense</a>
                                        @endif
                                    </span>
                                </td>
                                <td class="py-2">{{ $t->category_name ?? '—' }}</td>
                                <td class="py-2 text-right font-medium">
                                    @if ($t->type === 'income')
                                        <span class="text-emerald-600 dark:text-emerald-400">+৳ {{ number_format($t->amount, 2) }}</span>
                                    @else
                                        <span class="text-rose-600 dark:text-rose-400">-৳ {{ number_format($t->amount, 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-neutral-500">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $transactions->onEachSide(1)->links() }}
            </div>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                let expensePie = null;
                let incomeExpenseLine = null;

                function buildPie(data) {
                    const ctx = document.getElementById('expensePie')?.getContext('2d');
                    if (!ctx) return;
                    if (expensePie) expensePie.destroy();

                    expensePie = new Chart(ctx, {
                        type: 'pie',
                        data: { labels: data.labels, datasets: [{ data: data.values }] },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } }
                        }
                    });
                }

                function buildLine(data) {
                    const ctx = document.getElementById('incomeExpenseLine')?.getContext('2d');
                    if (!ctx) return;
                    if (incomeExpenseLine) incomeExpenseLine.destroy();

                    incomeExpenseLine = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Income',
                                    data: data.income,
                                    borderColor: 'rgb(34,197,94)',
                                    backgroundColor: 'rgba(34,197,94,0.2)',
                                    fill: true,
                                    tension: 0.3
                                },
                                {
                                    label: 'Expense',
                                    data: data.expense,
                                    borderColor: 'rgb(239,68,68)',
                                    backgroundColor: 'rgba(239,68,68,0.2)',
                                    fill: true,
                                    tension: 0.3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }

                // Initial build from server-rendered variables (first paint)
                document.addEventListener('DOMContentLoaded', () => {
                    buildPie(@json($chartDataPie));
                    buildLine(@json($chartDataLine));
                });

                // Livewire v3 events carrying fresh datasets each render
                window.addEventListener('chart-pie',  e => buildPie(e.detail.data ?? e.detail));
                window.addEventListener('chart-line', e => buildLine(e.detail.data ?? e.detail));

                // Keep charts sized correctly after any DOM updates
                document.addEventListener('livewire:init', () => {
                    Livewire.hook('message.processed', () => {
                        if (expensePie) expensePie.resize();
                        if (incomeExpenseLine) incomeExpenseLine.resize();
                    });
                });
            </script>
        @endpush
    </div>
</div>
