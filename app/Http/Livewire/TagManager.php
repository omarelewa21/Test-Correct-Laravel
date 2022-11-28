<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Tag;

class TagManager extends Component
{
    public $initWithTags = [];
    public $query;
    public $tags = [];
    public $highlightIndex;
    public $selectedTags = [];

    public function mount()
    {
        $this->resetValues();
        collect($this->initWithTags)->each(function($tag) {
            $this->selectedTags[$tag['uuid']] = $tag['name'];
        });
    }

    public function render()
    {
        return view('livewire.tag-manager')->layout('layouts.base');
    }

    public function resetValues()
    {
        $this->query = '';
        $this->highlightIndex = 0;
    }

    public function incrementHighlight()
    {
        if ($this->highlightIndex === count($this->tags) - 1) {
            $this->highlightIndex = 0;
            return;
        }
        $this->highlightIndex++;
    }

    public function decrementHighlight()
    {
        if ($this->highlightIndex === 0) {
            $this->highlightIndex = count($this->tags) - 1;
            return;
        }
        $this->highlightIndex--;
    }

    public function selectTag($clickIndex = null)
    {
        $tag = $this->tags[$this->highlightIndex] ?? null;

        if ($clickIndex != null) {
            $tag = $this->tags[$clickIndex];
        }

        if ($tag) {
            $this->selectedTags[$tag['uuid']] = $tag['name'];
            return;
        }

        $this->addQueryAsTag();
    }

    public function addQueryAsTag()
    {
        if (!$this->query) {
            return;
        }

        $tagExists = false;
        collect($this->selectedTags)->each(function ($tag, $id) use (&$tagExists) {
            if ($tag == $this->query) {
                $tagExists = true;
            }
        });

        if ($tagExists) {
            return;
        }

        //TODO: Create new tag model based on created question
        $tag = [
            'uuid' => (string)Str::uuid(),
            'name' => $this->query
        ];

        $this->selectedTags[$tag['uuid']] = $tag['name'];
    }

    public function updatedQuery()
    {
        $this->tags = Tag::select('uuid', 'name')->where('name', 'like', '%' . $this->query . '%')
            ->get()
            ->toArray();
    }

    public function removeSelectedTag($tagId)
    {
        unset($this->selectedTags[$tagId]);
    }
}
