<?php

namespace App\Livewire\Expense;

use App\Models\Expense;
use Developermithu\Tallcraftui\Traits\WithTcToast;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ExpenseModal extends Component
{
    use WithTcToast;

    public $expenseId = null;
    public $isView = false;

    public string $icon = '';

    public $source = null, $amount = null, $expense_date = null, $note = null, $category_id = null;

    public function rules(): array
    {
        return [
            'category_id' => 'nullable|exists:categories,id',
            'source' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'note' => 'nullable|string',
            'icon' => 'nullable|string|max:64',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category is invalid.',
            'source.required' => 'The expense source field is required.',
            'amount.required' => 'The amount field is required.',
            'expense_date.required' => 'The date field is required.',
        ];
    }


    public function updated($propertyName)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->validateOnly($propertyName);
    }

    // Submit the form data
    public function submit()
    {

        // dd($this->iconStyle);
        $this->validate();

        // Prepare the payload
        $saveData = [
            'user_id'    => Auth::user()->id,
            'category_id'       => $this->category_id,
            'source'       => $this->source,
            'amount'       => $this->amount,
            'expense_date'       => $this->expense_date,
            'note'       => $this->note,
            'icon'       => $this->icon,
        ];

        if ($this->expenseId) {
            $expense = Expense::find($this->expenseId);

            if (!$expense) {
                $this->error(
                    title: 'Expense not found!',
                    position: 'top-right',
                    showProgress: true,
                    showCloseIcon: true,
                );
                return;
            }

            // Check for changes WITHOUT persisting
            $original = $expense->getAttributes();
            $expense->fill($saveData);
            $hasChanges = $expense->isDirty();
            $expense->fill($original); 

            if (!$hasChanges) {
                $this->warning(
                    title: 'Nothing to update!',
                    position: 'top-right',
                    showProgress: true,
                    showCloseIcon: true,
                );
                $this->dispatch('expenses:refresh');
                Flux::modal('expense-modal')->close();
                return;
            }

            // Persist updates
            $expense->update($saveData);

            $this->success(
                title: 'expense updated successfully.',
                position: 'top-right',
                showProgress: true,
                showCloseIcon: true,
            );
        } else {
            Expense::create($saveData);

            // Reset fields you want cleared for a fresh form (adjust as needed)
            $this->reset();

            $this->success(
                title: 'Expense created successfully.',
                position: 'top-right',
                showProgress: true,
                showCloseIcon: true,
            );
        }

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

            $this->isView = false;
            $this->reset();
        } else {
            // dd($expense);
            $this->expenseId = $expense['id'];

            $this->source = $expense['source'];
            $this->amount = $expense['amount'];
            $this->expense_date = $expense['expense_date'];
            $this->category_id = $expense['category_id'];
            $this->note = $expense['note'];
            $this->icon = $expense['icon'];
        }
    }

    public function render()
    {

        return view('livewire.expense.expense-modal');
    }
}
