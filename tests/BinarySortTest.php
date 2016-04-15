<?php

use Faker\Factory;
use Speelpenning\BinarySearch\Collection;
use Symfony\Component\Stopwatch\Stopwatch;

class BinarySortTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param  int  $howMany
     * @return Collection
     */
    protected function createCollection($howMany)
    {
        $faker = Factory::create();

        $collection = new Collection();
        for ($i = 0; $i < $howMany; $i++) {
            // Create a record with a unique key and a random sentence.
            $random = new RandomObject($faker->unique()->randomNumber(), $faker->sentence());
            // Then push it into the collection.
            $collection->push($random);
        }

        // Return a presorted collection, so that we get a clear measurement.
        return $collection->sortIfNotSorted('key');
    }

    public function testItSupportsBinarySearch()
    {
        // First, we create a collection of 100 records.
        $collection = $this->createCollection(100);

        // We pick a random record from the collection which we want to find.
        $random = $collection->random(1);

        // Let's find the random object by key.
        $found = $collection->find('key', $random->key);

        $this->assertEquals($random, $found);
    }

    public function testEmptyCollections()
    {
        $collection = new Collection();

        $this->assertNull($collection->find('key', 1));
    }

    public function testItIsFasterThanSequentialSearchOnLargeDataSets()
    {
        // We want to measure performance, so we need a stopwatch.
        $stopwatch = new Stopwatch();

        // Create a collection with a large population.
        $stopwatch->start('creation');
        $collection = $this->createCollection(100000);
        $creation = $stopwatch->stop('creation');
        var_dump("\nIt took me {$creation->getDuration()}ms to create a collection of {$collection->count()} items.\n");

        // Pick a random record from the collection.
        $random = $collection->random(1);

        // Perform a sequential search and measure the duration.
        $stopwatch->start('sequential');
        $this->assertEquals($random, $collection->where('key', $random->key)->first());
        $sequential = $stopwatch->stop('sequential');
        var_dump("\nThe sequential search took {$sequential->getDuration()}ms.\n");

        // Perform a binary search and measure the duration.
        $stopwatch->start('binary');
        $this->assertEquals($random, $collection->find('key', $random->key));
        $binary = $stopwatch->stop('binary');
        var_dump("\nThe binary search took {$binary->getDuration()}ms.\n");

        // Assert that the binary duration is lower than the sequential duration.
        $this->assertLessThan($sequential->getDuration(), $binary->getDuration());
    }

}

/**
 * Class RandomObject, just to have an object for running tests.
 */
class RandomObject
{
    public $key;
    public $value;

    /**
     * Create a random object instance.
     *
     * @param string $key
     * @param string $value
     */
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
}
