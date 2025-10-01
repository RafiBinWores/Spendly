<?php

namespace App\Livewire\Category;

use App\Models\Category;
use Developermithu\Tallcraftui\Traits\WithTcToast;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

class CategoryModal extends Component
{
    use WithFileUploads;
    use WithTcToast;

    public $categoryId = null;
    public $isView = false;

    public string $icon = '';
    public string $iconStyle = 'o';

    // List of available Heroicons
    public array $availableIcons = [
        'academic-cap',
        'adjustments-horizontal',
        'adjustments-vertical',
        'archive-box',
        'archive-box-arrow-down',
        'archive-box-x-mark',
        'arrow-down',
        'arrow-down-circle',
        'arrow-down-left',
        'arrow-down-on-square',
        'arrow-down-on-square-stack',
        'arrow-down-right',
        'arrow-down-tray',
        'arrow-left',
        'arrow-left-circle',
        'arrow-left-end-on-rectangle',
        'arrow-left-start-on-rectangle',
        'arrow-long-down',
        'arrow-long-left',
        'arrow-long-right',
        'arrow-long-up',
        'arrow-path',
        'arrow-path-rounded-square',
        'arrow-right',
        'arrow-right-circle',
        'arrow-right-end-on-rectangle',
        'arrow-right-start-on-rectangle',
        'arrow-small-down',
        'arrow-small-left',
        'arrow-small-right',
        'arrow-small-up',
        'arrow-top-right-on-square',
        'arrow-trending-down',
        'arrow-trending-up',
        'arrow-up',
        'arrow-up-circle',
        'arrow-up-left',
        'arrow-up-on-square',
        'arrow-up-on-square-stack',
        'arrow-up-right',
        'arrow-up-tray',
        'arrow-uturn-down',
        'arrow-uturn-left',
        'arrow-uturn-right',
        'arrow-uturn-up',
        'arrows-pointing-in',
        'arrows-pointing-out',
        'arrows-right-left',
        'arrows-up-down',
        'at-symbol',
        'backspace',
        'backward',
        'banknotes',
        'bars-2',
        'bars-3',
        'bars-3-bottom-left',
        'bars-3-bottom-right',
        'bars-3-center-left',
        'bars-4',
        'bars-arrow-down',
        'bars-arrow-up',
        'battery-0',
        'battery-50',
        'battery-100',
        'beaker',
        'bell',
        'bell-alert',
        'bell-slash',
        'bell-snooze',
        'bolt',
        'bolt-slash',
        'book-open',
        'bookmark',
        'bookmark-slash',
        'briefcase',
        'bug-ant',
        'building-library',
        'building-office',
        'building-office-2',
        'building-storefront',
        'cake',
        'calculator',
        'calendar',
        'calendar-days',
        'camera',
        'chart-bar',
        'chart-bar-square',
        'chart-pie',
        'chat-bubble-bottom-center',
        'chat-bubble-bottom-center-text',
        'chat-bubble-left',
        'chat-bubble-left-ellipsis',
        'chat-bubble-left-right',
        'chat-bubble-oval-left',
        'chat-bubble-oval-left-ellipsis',
        'check',
        'check-badge',
        'check-circle',
        'chevron-double-down',
        'chevron-double-left',
        'chevron-double-right',
        'chevron-double-up',
        'chevron-down',
        'chevron-left',
        'chevron-right',
        'chevron-up',
        'chevron-up-down',
        'circle-stack',
        'clipboard',
        'clipboard-document',
        'clipboard-document-check',
        'clipboard-document-list',
        'clock',
        'cloud',
        'cloud-arrow-down',
        'cloud-arrow-up',
        'code-bracket',
        'code-bracket-square',
        'cog',
        'cog-6-tooth',
        'cog-8-tooth',
        'command-line',
        'computer-desktop',
        'cpu-chip',
        'credit-card',
        'cube',
        'cube-transparent',
        'currency-bangladeshi',
        'currency-dollar',
        'currency-euro',
        'currency-pound',
        'currency-rupee',
        'currency-yen',
        'cursor-arrow-rays',
        'cursor-arrow-ripple',
        'device-phone-mobile',
        'device-tablet',
        'document',
        'document-arrow-down',
        'document-arrow-up',
        'document-chart-bar',
        'document-check',
        'document-duplicate',
        'document-magnifying-glass',
        'document-minus',
        'document-plus',
        'document-text',
        'ellipsis-horizontal',
        'ellipsis-horizontal-circle',
        'ellipsis-vertical',
        'envelope',
        'envelope-open',
        'exclamation-circle',
        'exclamation-triangle',
        'eye',
        'eye-dropper',
        'eye-slash',
        'face-frown',
        'face-smile',
        'film',
        'finger-print',
        'fire',
        'flag',
        'folder',
        'folder-arrow-down',
        'folder-minus',
        'folder-open',
        'folder-plus',
        'forward',
        'funnel',
        'gif',
        'gift',
        'gift-top',
        'globe-alt',
        'globe-americas',
        'globe-asia-australia',
        'globe-europe-africa',
        'hand-raised',
        'hand-thumb-down',
        'hand-thumb-up',
        'hashtag',
        'heart',
        'home',
        'home-modern',
        'identification',
        'inbox',
        'inbox-arrow-down',
        'inbox-stack',
        'information-circle',
        'key',
        'language',
        'lifebuoy',
        'light-bulb',
        'link',
        'list-bullet',
        'lock-closed',
        'lock-open',
        'magnifying-glass',
        'magnifying-glass-circle',
        'magnifying-glass-minus',
        'magnifying-glass-plus',
        'map',
        'map-pin',
        'megaphone',
        'microphone',
        'minus',
        'minus-circle',
        'minus-small',
        'moon',
        'musical-note',
        'newspaper',
        'no-symbol',
        'paint-brush',
        'paper-airplane',
        'paper-clip',
        'pause',
        'pause-circle',
        'pencil',
        'pencil-square',
        'phone',
        'phone-arrow-down-left',
        'phone-arrow-up-right',
        'phone-x-mark',
        'photo',
        'play',
        'play-circle',
        'play-pause',
        'plus',
        'plus-circle',
        'plus-small',
        'power',
        'presentation-chart-bar',
        'presentation-chart-line',
        'printer',
        'puzzle-piece',
        'qr-code',
        'question-mark-circle',
        'queue-list',
        'radio',
        'receipt-percent',
        'receipt-refund',
        'rectangle-group',
        'rectangle-stack',
        'rocket-launch',
        'rss',
        'scale',
        'scissors',
        'server',
        'server-stack',
        'share',
        'shield-check',
        'shield-exclamation',
        'shopping-bag',
        'shopping-cart',
        'signal',
        'signal-slash',
        'sparkles',
        'speaker-wave',
        'speaker-x-mark',
        'square-2-stack',
        'square-3-stack-3d',
        'squares-2x2',
        'squares-plus',
        'star',
        'stop',
        'stop-circle',
        'sun',
        'swatch',
        'table-cells',
        'tag',
        'ticket',
        'trash',
        'trophy',
        'truck',
        'tv',
        'user',
        'user-circle',
        'user-group',
        'user-minus',
        'user-plus',
        'users',
        'variable',
        'video-camera',
        'video-camera-slash',
        'view-columns',
        'viewfinder-circle',
        'wallet',
        'wifi',
        'window',
        'wrench',
        'wrench-screwdriver',
        'x-circle',
        'x-mark'
    ];



    public $name = null, $status = 'active';

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name,' . $this->categoryId,
            'icon'             => 'nullable|string|max:64',
            'iconStyle'        => 'required|in:o,s',
            'status' => 'required|in:active,disable',
        ];
    }

    // Submit the form data
    public function submit()
    {
        $this->validate();

        // Prepare the payload once (no image handling here)
        $saveData = [
            'user_id'    => Auth::user()->id,
            'name'       => $this->name,
            'slug'       => Str::slug($this->name),
            'icon'       => $this->icon,
            'icon_style' => $this->iconStyle,
            'status'     => $this->status,
        ];

        if ($this->categoryId) {
            $category = Category::find($this->categoryId);

            if (!$category) {
                $this->error(
                    title: 'Category not found!',
                    position: 'top-right',
                    showProgress: true,
                    showCloseIcon: true,
                );
                return;
            }

            // Check for changes WITHOUT persisting
            $original = $category->getAttributes();
            $category->fill($saveData);
            $hasChanges = $category->isDirty();
            $category->fill($original); // revert to original before actual update

            if (!$hasChanges) {
                $this->warning(
                    title: 'Nothing to update!',
                    position: 'top-right',
                    showProgress: true,
                    showCloseIcon: true,
                );
                $this->dispatch('categories:refresh');
                Flux::modal('category-modal')->close();
                return;
            }

            // Persist updates
            $category->update($saveData);

            $this->success(
                title: 'Category updated successfully.',
                position: 'top-right',
                showProgress: true,
                showCloseIcon: true,
            );
        } else {
            Category::create($saveData);

            // Reset fields you want cleared for a fresh form (adjust as needed)
            $this->reset(['name', 'icon', 'iconStyle']);
            $this->status = 'active';

            $this->success(
                title: 'Category created successfully.',
                position: 'top-right',
                showProgress: true,
                showCloseIcon: true,
            );
        }

        $this->dispatch('categories:refresh');
        Flux::modal('category-modal')->close();
    }


    #[On('open-category-modal')]
    public function categoryDetail($mode, $category = null)
    {

        $this->isView = $mode === 'view';

        if ($mode === 'create') {

            $this->isView = false;
            $this->reset();
            $this->status = 'active';
        } else {
            // dd($category);
            $this->categoryId = $category['id'];

            $this->name = $category['name'];
            $this->icon = $category['icon'];
            $this->iconStyle = $category['icon_style'];
            $this->status = $category['status'];
        }
    }

    public function render()
    {
        $icons = json_decode(file_get_contents(resource_path('data/heroicons.json')), true);

        return view('livewire.category.category-modal', [
            'availableIcons' => $icons,
        ]);
    }
}
