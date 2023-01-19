# PHP client library for consuming the FileMaker Data API

PHP package (client library) for consuming the FileMaker Data API, with Laravel support. 

## Install

Via Composer:

```bash
composer require flooris/filemaker-data-api
```


## Basic Usage

```php
// Include Composer's autoloader.
require_once __DIR__ . '/vendor/autoload.php';

// Define the pagination variables
$offset = 1;
$limit = 100;

// Define the FileMaker Lay-out
$layoutName = 'Products';

// Set up the FileMaker find query
$findQuery = [
    'some_field_name' => 'text to find',
];

// Set up the client.
$client = new \Flooris\FileMakerDataApi\Client();

$result = $client
    ->record($layoutName)
    ->findRecords($findQuery, $offset, $limit)

$totalRecordCount   = $result->dataInfo->totalRecordCount;
$foundCount         = $result->dataInfo->foundCount;
$currentResultCount = $result->dataInfo->returnedCount;

foreach ($result->data as $fmResultObject) {
    $recordId       = $fmResultObject->recordId;
    $modificationId = $fmResultObject->modId;
    
    $title  = $fmResultObject->fieldData->title;
    $active = (bool)$fmResultObject->fieldData->active;
        
    $options = $fmResultObject->portalData->options;
}
```

## Advanced usage

Define a Model Class for a FileMaker record, for example: `FmBrandObject`.
The Model Class should extend `FmBaseObject`.

```php

use Flooris\FileMakerDataApi\RecordRepository\FmBaseObject;

class FmBrandObject extends FmBaseObject
{
    public const FM_LAYOUT_NAME = 'php_BRAND';
    public const FM_ID_FIELD_NAME = '_id_brand';

    public function getId(): int
    {
        return $this->getValue(self::FM_ID_FIELD_NAME);
    }

    public function getDataArray(): array
    {
        return [
            'slug'        => $this->getValue('t_slug'),
            'name'        => $this->getValue('t_name'),
            'description' => $this->getValue('t_description'),
            'position'    => (int)$this->getValue('n_sortOrder'),
            'active'      => $this->getValueAsBoolean('t_active'),
        ];
    }

    public function getSupplierId(): int
    {
        return (int)$this->getValue('id_supplier');
    }
}
```

Define a Repository Class, for example: `FmBrandRepository`.
The Repository Class should extend `FmBaseRepository`.

The Repository Class mainly defines the Model Class, which also contains the FileMaker Layout name and ID field name.

```php
use Flooris\FileMakerDataApi\Client;
use Flooris\FileMakerDataApi\RecordRepository\FmBaseRepository;

class FmBrandRepository extends FmBaseRepository
{
    public function __construct(
        private readonly Client $fmClient
    )
    {
        parent::__construct(
            $this->fmClient,
            FmBrandObject::FM_LAYOUT_NAME,
            FmBrandObject::FM_ID_FIELD_NAME
        );
    }

    public function find(int $id): ?FmBrandObject
    {
        if ($fmDataRecord = $this->findRecordById($id)) {
            return new FmBrandObject($fmDataRecord);
        }

        return null;
    }

    public function each(callable $callback): void
    {
        parent::each(function (\stdClass $fmDataRecord) use ($callback) {
            $callback(new FmBrandObject($fmDataRecord));
        });
    }
}
```

With the Repository it is easy to access records from the FileMaker database table.
For example:

```php

// Set up the client.
$client = new \Flooris\FileMakerDataApi\Client();

// Initialize the Brand Repository
$brandRepository = new FmBrandRepository($client);

$brandRepository->each(function(FmBrandObject $fmBrandObject) {
    $id         = $fmBrandObject->getId();
    $supplierId = $fmBrandObject->getSupplierId();
    
    // For example update or create an Eloquent Model like this:
    Brand::query()->updateOrCreate(
        ['id' => $id], 
        $fmBrandObject->getDataArray()
    );
    
    // Or for example update or create an Eloquent Model using a BelongsTo relationship like this:
    Supplier::query()
        ->findOrFail($supplierId)
        ->brands()
        ->updateOrCreate(
            ['id' => $id], 
            $fmBrandObject->getDataArray()
        );
})

```


## Package improvements

ToDo's:
* Tests
* Custom exceptions
