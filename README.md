# Url-Builder
![Coverage](coverage/badge.svg)

A lightweight library with many features to easy build URLs

- [Features](#bookmark_tabs-features)
- [Installation](#hammer_and_wrench-installation)
- [Usage](#memo-usage)
  - [Create from existing URL](#create-from-existing-url)
  - [Handle path](#handle-path)
  - [Handle query param](#handle-query-param)
  - [Work with parent](#work-with-parent)
  - [Get relative path](#get-relative-path)
  - [Get query params in string](#get-query-params-in-string)
  - [Convert full URL to string](#convert-full-url-to-string)
- [Advanced](#memo-advanced)
  - [Compare URL to another](#compare-url-to-another)
  - [Get word between two others](#get-word-between-two-others)
  - [Split path from string (static)](#split-path-from-string-static)
  - [Trim path from string (static)](#trim-path-from-string-static)
- [API](#gear-api)
- [Licence](#balance_scale-licence)
- [Authors](#busts_in_silhouette-authors)
- [Contributors](#handshake-contributors)

## :bookmark_tabs: Features
This library allows :
- Create URLs most easly
- Parse and decompose your URLs
- Ride up in the URL tree
- Make comparisons between URLs

## :hammer_and_wrench: Installation
To import the library you just need to run this command :
```shell
composer require innova2/url-builder
```

## :memo: Usage
### Create from existing URL
```php
$url = UrlBuilder::createFromUrl('http://localhost:8080/users');
// or create new url with the constructor
```

### Handle path
Add new path segment(s)
```php
$userId = '170b16cd-ad47-4c9c-86cf-7c83bd40d775';
$url->addPath(':id/comments')->addParam('id', $userId);
```
Add multiples parameters
```php
$userId = '170b16cd-ad47-4c9c-86cf-7c83bd40d775';
$commentId = '218dd1c4-0bb0-425a-be0b-85427304e100';
$url->addPath(':userId/comments/:commentId')->addParams([ 
    'userId' => $userId, 
    'commentId' => $commentId
]);
```
Get the first path segment
```php
$rowNum = 10;
$url = UrlBuilder::createFromUrl('http://localhost:8080/rows/:rowNum/cells')->addParam('rowNum', $rowNum);
$url->getFirstPath(); // Output: 'rows'
```
Get the last path segment
```php
$url->getLastPath(); // Output: 'cells'
```

### Handle query param
Add new query param
```php
$page = 2;
$url->addQuery('page', $page);
```
Add multiples query params
```php
$page = 2;
$order = 'DESC';
$url->addQueries([
    'page' => $page, 
    'order' => $order
]);
```

### Work with parent
Get parent URL easly.<br>
*This function return a new instance of UrlBuilder*
```php
$url = UrlBuilder::createFromUrl('http://localhost:8080/orders/:orderId/products/:productId');
$parent = $url->getParent(); // Get 'http://localhost:8080/orders/:orderId/products'
```
Or up to the specific level
```php
$url->getParent(3); // Get 'http://localhost:8080/orders'
```

### Get relative path
Retrieve the relative path in string format
```php
$postId = 'a937b39e-9664-404a-ac56-f3da2b83a951';
$url = UrlBuilder::createFromUrl('http://localhost:8080/posts/:id')->addParam('id', $postId);
$url->getRelativePath(); // Output: '/posts/a937b39e-9664-404a-ac56-f3da2b83a951'
```
And with query params<br>
*Don't forget to add 'true' parameter to allow query params conversion*
```php
$url->addQuery('displaySimilar', true);
$url->getRelativePath(); // Output: '/posts/a937b39e-9664-404a-ac56-f3da2b83a951'
$url->getRelativePath(true); // Output: '/posts/a937b39e-9664-404a-ac56-f3da2b83a951?displaySimilar=true'
```

### Get query params in string
Retrieve the query params in string format
```php
$url = UrlBuilder::createFromUrl('http://localhost:8080/vehicles')->addQueries([
  'page' => '2',
  'order' => 'ASC'
]);
$url->getQueryString(); // Output: '?page=2&order=ASC'
```

### Convert full URL to string
Retrieve the query params in string format
```php
$name = 'url-builder';
$url = UrlBuilder::createFromUrl('https://github.com/InnovA2')
        ->addPath(':name/pulls')
        ->addParam('name', $name);
$url->toString(); // Output: 'https://github.com/InnovA2/url-builder/pulls'
```

## :memo: Advanced
### Compare URL to another
Compare the current URL to another URL (UrlBuilder instance)
```php
$id = '434f65eb-4e5f-4b29-899c-b3e159fff61c';
$id2 = '3e972ca2-b422-4ac9-b793-e6f305c7bfb2';
$url = UrlBuilder::createFromUrl('http://localhost:8080/users/:id')->addParam('id', $id);
$url2 = UrlBuilder::createFromUrl('http://localhost:8080/users/:id')->addParam('id', $id);
$url3 = UrlBuilder::createFromUrl('http://localhost:8080/users/:id')->addParam('id', $id2);
$url->compareTo($url2); // Output: true
$url->compareTo($url3); // Output: false
```

### Get word between two others
Compare the current URL to another URL (UrlBuilder instance)
```php
$id = '434f65eb-4e5f-4b29-899c-b3e159fff61c';
$url = UrlBuilder::createFromUrl('http://localhost:8080/users/:id')->addParam('id', $id);
$url->compareTo($url2); // Output: true
$url->compareTo($url3); // Output: false
```

### Split path from string (static)
Split path string by slash
```php
UrlBuilder::splitPath('/InnovA2/url-builder/pulls/'); // Output: ['InnovA2', 'url-builder', 'pulls']
// or if you have more slashes
UrlBuilder::splitPath('/InnovA2///url-builder/pulls/'); // Output: ['InnovA2', 'url-builder', 'pulls']
```

### Trim path from string (static)
Trim path string by removing useless slashes
```php
UrlBuilder->trimPath('/InnovA2/url-builder/pulls/'); // Output: 'InnovA2/url-builder/pulls'
// or if you have more slashes
UrlBuilder->trimPath('/InnovA2///url-builder/pulls/'); // Output: 'InnovA2/url-builder/pulls'
```

## :gear: API
```php
static function createFromUrl(string $baseUrl): UrlBuilder
static function splitPath(string $path): array
static function trimPath(string $path): string
function compareTo(UrlBuilder $url, bool $relative = true): bool
function getScheme(): string
function getHost(): string
function getPort(): int
function getPaths(): array
function setPort(int $port): UrlBuilder
function addPath(string $path): UrlBuilder
function addParam(string $key, $value): UrlBuilder
function addParams(array $params): UrlBuilder
function getParams(): array
function addQuery(string $key, $value): UrlBuilder
function addQueries(array $queries): UrlBuilder
function getQuery(): array
function getFirstPath(): string
function getLastPath(): string
function getParent(int $n = 1): UrlBuilder
function getBetween2Words(string $a, string $b): ?string
function getRelativePath(bool $query = false): string
function getQueryString(): ?string
function toString(): string
```
    
## :balance_scale: Licence
[MIT](LICENSE)

## :busts_in_silhouette: Authors
- [Adrien MARTINEAU](https://github.com/WaZeR-Adrien)
- [Angéline TOUSSAINT](https://github.com/AngelineToussaint)

## :handshake: Contributors
Do not hesitate to participate in the project!
Contributors list will be displayed below.
