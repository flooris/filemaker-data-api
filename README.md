# filemaker-data-api
Package to easily access the FileMaker Data API

```
$client  = new Client();
$records = $client->record('layout-name');

$singleRecord = $records->singleRecord(1);
```