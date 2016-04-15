# Binary search on Laravel's collection

[![Build Status](https://travis-ci.org/Speelpenning-nl/binary-search-collection.svg)](https://travis-ci.org/Speelpenning-nl/binary-search-collection)
[![codecov.io](http://codecov.io/github/Speelpenning-nl/binary-search-collection/coverage.svg?branch=master)](http://codecov.io/github/Speelpenning-nl/binary-search-collection?branch=master)
[![Latest Stable Version](https://poser.pugx.org/speelpenning/binary-search-collection/version)](https://packagist.org/packages/speelpenning/binary-search-collection)
[![Latest Unstable Version](https://poser.pugx.org/speelpenning/binary-search-collection/v/unstable)](//packagist.org/packages/speelpenning/binary-search-collection)
[![License](https://poser.pugx.org/speelpenning/binary-search-collection/license)](https://packagist.org/packages/speelpenning/binary-search-collection)

This package is useful when working with large data sets that contain objects with an unique identifier.

## Getting started

### Requirements

* illuminate/support: >= 5.0

### Installation

Pull in the package by Composer:
```bash
composer require speelpenning/binary-search-collection
```

### Usage

If you have structured data stored in different formats like csv and XML and you need to combine the data for a 
report. The only thing you need to do is to define some models and create a reader which returns a collection 
with the models.

### Example use case

Say we have the the following files:

* An XML file with 200.000 products (product number, description, etc.)
* A csv price list with 70.000 prices (product number, gross price, net price, list price)

For sending a price list to "our customers" we need to make a csv file with product number, description, 
list price and gross price. 

First we need two models, say Product and Price, which hold the attributes as defined in the files. Next, we create 
a reader for the XML (ie. ProductsReader) file and the csv file (ie. PriceReader). The readers accept a binary 
sort collection in the constructor and fills the collection with models from the file. The collections is returned 
by the readers.

Now we come near our goal: generating a csv price list for our customers. For this, we create a writer (say 
CustomerPriceListWriter), which accepts both readers in the constructor. The writer iterates over the collection 
with Price models, while finding the Product records in the other collection using binary search. Then the data 
from both models is combined and written to the csv file with customer prices.

```php
<?php

class CustomerPriceListWriter
{
    /**
     * @var ProductReader
     */
    protected $productReader;

    /**
     * @var PriceReader
     */
    protected $priceReader;

    /**
     * Create a new customer price list writer instance.
     *
     * @param ProductReader $productReader
     * @param PriceReader $priceReader
     */
    public function __construct(ProductReader $productReader, PriceReader $priceReader)
    {
        $this->productReader = $productReader;
        $this->priceReader = $priceReader;
    }


    protected function write($productsFilename, $pricesFilename, $targetFilename = null)
    {
        // First we read the products and prices file, which both
        // return a collection that supports binary search.
        $products = $this->productReader->read($productsFilename);
        $prices = $this->priceReader->read($pricesFilename);

        // Prepare the array with CSV lines.
        $csv = [$this->toCsv([
            'Product Number',
            'Description',
            'List price',
            'Your price'
        ])];
        foreach ($prices as $price) {
            $product = $products->find($price->product_number);

            $csv[] = $this->toCsv([
                $product->product_number,
                $product->description,
                $price->list_price,
                $price->gross_price
            ]);
        }

        $targetFilename = is_null($targetFilename) ? 'customer-prices-'.date('YmdHis').'.csv' : $targetFilename;
        file_put_contents($targetFilename, implode("\n", $csv));
    }

    protected function toCsv(array $values)
    {
        foreach ($values as $key => $value) {
            $value[$key] = is_numeric($value) ? $value : "\"{$value}\"";
        }
        return implode(',', $values);
    }
}
```