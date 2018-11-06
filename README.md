# PHP-Iconfinder
Iconfinder PHP SDK for simple queries against the millions of icons provided by Iconfinder

## Example

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
