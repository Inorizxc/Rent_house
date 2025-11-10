<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\House;
use App\Models\User;

class HousesPage extends Component
{
    use WithPagination;

    public string $searchInput = '';
    public string $search = '';

    public function mount(string $searchInput = '')
    {
        $this->searchInput = $searchInput;
        $this->applySearch();
    }

    public function updatingSearchInput() { $this->resetPage(); }

    public function applySearch()
    {
        $this->search = trim($this->searchInput);
        $this->resetPage();
    }

    public function render()
    {
        $houses = House::query()
            ->with(['user']) // добавь другие связи, когда они будут
            ->when($this->search, function($q) {
                $s = "%{$this->search}%";
                $q->where(function($w) use ($s){
                    $w->where('adress','like',$s)
                      ->orWhere('area','like',$s);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('livewire.houses-page', compact('houses'));
    }
}
