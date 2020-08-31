# PHP-Iconfinder
PHP SDK for running queries against the millions of icons provided by
[Iconfinder](https://iconfinder.com). Includes recursive searches.

### Supports
- Searches
- SVG downloading

### Sample Search
``` php
$client = new getstencil\Iconfinder\Iconfinder();
$client->setAPIKey('***');
$client->setAPISecret('***');
$client->setLimit(10);
$client->setOffset(0);
$results = $client->search('love') ?? array();
print_r($results);
exit(0);
```

### Sample Download
``` php
$client = new getstencil\Iconfinder\Iconfinder();
$client->setAPIKey('***');
$client->setAPISecret('***');
$content = $client->getPath('/path/to/svg') ?? 'Could not load content';
echo $content;
exit(0);
```

### Note
Requires
[PHP-RemoteRequests](https://github.com/onassar/PHP-RemoteRequests).
