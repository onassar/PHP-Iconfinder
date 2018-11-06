# PHP-Iconfinder
Simple [Iconfinder](https://iconfinder.com) PHP SDK for simple queries against the millions of icons provided by [Iconfinder](https://iconfinder.com).

## Search Example

``` php
require_once '/path/to/Iconfinder.php';
$id = '***';
$secret = '***';
$client = new Iconfinder($id, $secret);
$query = 'fish';
$options = array(
    'limit' => 10,
    'offset' => 0
);
$response = $client->getIconsByTerm($query, $options);
```

## Icon Download Example

``` php
require_once '/path/to/Iconfinder.php';
$id = '***';
$secret = '***';
$client = new Iconfinder($id, $secret);
$path = '/icons/123/formats/svg/456/download'
$response = $client->getPath($path);
```
