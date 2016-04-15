<?php

namespace Speelpenning\BinarySearch;

use Illuminate\Support\Collection as LaravelCollection;

class Collection extends LaravelCollection
{
    /**
     * The key on which the items are sorted.
     *
     * @var string|null
     */
    protected $sortKey = null;

    /**
     * Finds an item by key and value. If the item is not found
     * in the collection, the default will be returned.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  mixed  $default
     * @return mixed
     */
    public function find($key, $value, $default = null)
    {
        // First, we create a new collection with the values of the current
        // collection sorted by the given key. We only retrieve the values
        // in order to get a fresh numeric key index. Otherwise binary
        // search simply won't work.
        $this->sortIfNotSorted($key);

        // Next we attempt to get the pointer of the item we are looking
        // for. This is done recursively by passing on the key to search
        // on, the value to look for, the sorted collection and the
        // index key of the first and last item in the collection.
        $pointer = $this->getPointerUsingBinarySearch($key, $value, 0, $this->count() - 1);

        // Return the item corresponding with the found pointer. If no
        // pointer was found, the default value is returned.
        return $this->get($pointer, $default);
    }

    /**
     * Sorts the items in the collection if the given key
     * differs from the current sort key.
     *
     * @param  string  $key
     * @return static
     */
    public function sortIfNotSorted($key)
    {
        if ($this->sortKey != $key) {

            // First, we replace the current array of items with a sorted one.
            $this->items = $this->sortBy($key)->values()->toArray();

            // Then we set the sort key to the given key.
            $this->sortKey = $key;
        }
        return $this;
    }

    /**
     * Attempts to find the pointer of the item using binary search.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $left
     * @param  int  $right
     * @return int|null
     */
    protected function getPointerUsingBinarySearch($key, $value, $left, $right)
    {
        if ($left > $right) {
            return null;
        }

        $mid = (int)(($left + $right) / 2);
        $itemValue = data_get($this->items[$mid], $key);

        if ($itemValue === $value) {
            return $mid;
        }
        elseif ($itemValue > $value) {
            return $this->getPointerUsingBinarySearch($key, $value, $left, $mid - 1);
        }
        elseif ($itemValue < $value) {
            return $this->getPointerUsingBinarySearch($key, $value, $mid + 1, $right);
        }
    }
}