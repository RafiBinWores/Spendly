<?php

namespace App\Livewire\Expense;

use App\Models\Expense;
use Developermithu\Tallcraftui\Traits\WithTcToast;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ExpenseModal extends Component
{
    use WithFileUploads, WithTcToast;

    public $expenseId = null;
    public $isView = false;

    public array $newFiles = [];
    public array $files = [];
    public array $existingFiles = [];

    public string $icon = '';

    public $source = null, $amount = null, $expense_date = null, $note = null, $category_id = null;

    public function rules(): array
    {
        return [
            'category_id'   => 'nullable|exists:categories,id',
            'source'        => 'required|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'expense_date'  => 'required|date',
            'note'          => 'nullable|string',
            'icon'          => 'nullable|string|max:64',
            'newFiles.*'    => 'nullable|file|max:5120|mimes:jpg,jpeg,png,webp,svg,pdf,xls,xlsx,csv',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists'  => 'The selected category is invalid.',
            'source.required'     => 'The expense source field is required.',
            'amount.required'     => 'The amount field is required.',
            'expense_date.required' => 'The date field is required.',
        ];
    }

    public function updated($propertyName)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->validateOnly($propertyName);
    }

    /**
     * IMPORTANT: When user picks more files, append them (don’t replace).
     */
    public function updatedNewFiles(): void
    {

        $this->validateOnly('newFiles.*');
        $this->files = array_values(array_merge($this->files, $this->newFiles));
        $this->reset('newFiles');
    }

    public function clearFile($index = null)
    {
        if (is_null($index)) {
            $this->files = [];
            return;
        }
        unset($this->files[$index]);
        $this->files = array_values($this->files);
    }

    public function clearExistingFile($index)
    {
        $path = $this->existingFiles[$index] ?? null;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        unset($this->existingFiles[$index]);
        $this->existingFiles = array_values($this->existingFiles);
    }

    protected function storeUploadedFiles(): array
    {
        $paths = [];

        // Sanitize source (fallback "file")
        $base = trim((string)($this->source ?? 'file'));
        // broader than slug: replace non-alnum with underscore
        $base = preg_replace('/[^A-Za-z0-9_\-]/', '_', $base) ?: 'file';

        // Timestamp (seconds)
        $stamp = now()->format('Ymd_His');

        foreach ($this->files as $i => $f) {
            $ext = strtolower($f->getClientOriginalExtension());
            $counter = str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT);
            $filename = "{$base}_{$stamp}_{$counter}.{$ext}";
            $paths[] = $f->storeAs('uploads/expenseFiles', $filename, 'public');
        }

        return $paths;
    }

    public function submit()
    {
        // validate everything, including last temp picks if any
        $this->validate();

        // store new ones (from the accumulated $files)
        $newPaths = $this->storeUploadedFiles();
        $allPaths = array_merge($this->existingFiles, $newPaths);

        $saveData = [
            'user_id'      => Auth::id(),
            'category_id'  => $this->category_id,
            'source'       => $this->source,
            'amount'       => $this->amount,
            'expense_date' => $this->expense_date,
            'files'        => $allPaths,   // JSON array in DB
            'note'         => $this->note,
            'icon'         => $this->icon,
        ];

        if ($this->expenseId) {
            $expense = Expense::find($this->expenseId);
            if (!$expense) {
                $this->error(title: 'Expense not found!', position: 'top-right', showProgress: true, showCloseIcon: true);
                return;
            }
            $expense->update($saveData);
            $this->success(title: 'Expense updated successfully.', position: 'top-right', showProgress: true, showCloseIcon: true);
        } else {
            Expense::create($saveData);
            $this->success(title: 'Expense created successfully.', position: 'top-right', showProgress: true, showCloseIcon: true);
        }

        // reset staged uploads; keep the DB-saved list in UI if reopening
        $this->reset(['files','newFiles','existingFiles','source','amount','expense_date','note','icon','category_id']);
        $this->dispatch('expenses:refresh');
        Flux::modal('expense-modal')->close();
    }

    #[On('open-expense-modal')]
    public function expenseDetail($mode, $expense = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->isView = $mode === 'view';

        if ($mode === 'create') {
            $this->reset();
            $this->files = [];
            $this->newFiles = [];
            $this->existingFiles = [];
            return;
        }

        $this->expenseId     = $expense['id'] ?? null;
        $this->source        = $expense['source'] ?? null;
        $this->amount        = $expense['amount'] ?? null;
        $this->expense_date  = $expense['expense_date'] ?? null;
        $this->category_id   = $expense['category_id'] ?? null;
        $this->note          = $expense['note'] ?? null;
        $this->icon          = $expense['icon'] ?? '';

        $this->existingFiles = !empty($expense['files']) ? $expense['files'] : [];
        $this->files = [];
        $this->newFiles = [];
    }

    public function render()
    {
        return view('livewire.expense.expense-modal');
    }
}
