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

## Example

-- Will be completed with code examples soon --

Say we have the the following files:

* An XML file with 200.000 products (product number, description, etc.)
* A csv price list with 70.000 prices (product number, gross price, net price, list price)

For spreading a price list to "our customers" we need to make a csv file with product number, description, 
list price and gross price. 

First we need two models, say Product and RawPrice, which hold the attributes as defined in the files. Next, we create 
a reader for the XML (ie. ProductsReader) file and the csv file (ie. RawPriceReader). The readers accept a binary 
sort collection in the constructor and fills the collection with models from the file. The collections is returned 
by the readers.

Now we come near our goal: write a csv price list for our customers. For this, we create a writer (say 
CustomerPriceWriter), which accepts both readers in the constructor. The writer iterates over the collection with 
RawPrice models, while finding the Product records in the other collection using binary search. Then the data from
both models is combined and written to the csv file with customer prices.
