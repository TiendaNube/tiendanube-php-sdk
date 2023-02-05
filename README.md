# Nuvemshop/Tiendanube SDK for PHP

This SDK provides a simplified access to the [API](https://tiendanube.github.io/api-documentation/) of [Nuvemshop](https://www.nuvemshop.com.br) / [Tiendanube](https://www.tiendanube.com).

## Requirements

PHP 7.4.0 and later.

## Composer

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require stripe/stripe-php
```

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading):

```php
require_once('vendor/autoload.php');
```

## Authenticating Your App

When a user installs your app, he will be taken to your specified Redirect URI with a parameter called `code` containing your temporary authorization code.

With this code you can request a permanent access token.

```php
use Tiendanube\Context;
use Tiendanube\Auth\OAuth;

$context = Context::initialize(
    CLIENT_ID,
    CLIENT_SECRET,
    APP_BASE_URL,
    APP_USER_AGENT_PREFIX,
);

$oauth = new OAuth();
$session = $oauth->callback($_GET);
```

The returned session will contain the id of the authenticated store, as well as the access token and the authorized scopes.

```php
var_dump($session);
//object(Tiendanube\Auth\Session)#5 (3) {
//  ["storeId":"Tiendanube\Auth\Session":private]=>
//  string(4) "1234"
//  ["scope":"Tiendanube\Auth\Session":private]=>
//  string(40) "read_products,read_orders,read_customers"
//  ["accessToken":"Tiendanube\Auth\Session":private]=>
//  string(40) "a2b544066ee78926bd0dfc8d7bd784e2e016b422"
//}
```

Keep in mind that future visits to your app will not go through the Redirect URI, so you should store the session.

However, if you need to authenticate a user that has already installed your app (or invite them to install it), you can redirect them to login to the Nuvemshop/Tiendanube site.

```php
use Tiendanube\Auth\OAuth;
$auth = new OAuth();

//You can use one of these to obtain a url to login to your app
$url = $auth->loginUrlBrazil();
$url = $auth->loginUrlSpLATAM();

//Redirect to $url
```

After the user has logged in, he will be taken to your specified Redirect URI with a new authorization code. You can use this code to request a new request token.


Making a Request
----------------
The first step is to instantiate the `API` class with a store id and an access token, as well as a [user agent to identify your app](https://github.com/TiendaNube/api-docs#identify-your-app). Then you can use the `get`, `post`, `put` and `delete` methods.

```php

use Tiendanube\Context;
use Tiendanube\Auth\Session;
use Tiendanube\Rest\Adminv1\Product;

$context = Context::initialize(
    CLIENT_ID,
    CLIENT_SECRET,
    'www.awesome-app.com',
    'Awesome App (contact@awesome.com)'
);

$session = new Session(
    STORE_ID,
    ACCESS_TOKEN,
    SCOPES
);

$productsFromFirstPage = Product::all($session);
var_dump($productsFromFirstPage);

//You can then access following pages with the same object
$productsFromSecondPage = Product::all($session, Product::$nextPageQuery);
```

You can also call the endpoints directly

```php
use Tiendanube\Context;
use Tiendanube\Auth\Session;

$context = Context::initialize(
    CLIENT_ID,
    CLIENT_SECRET,
    'www.awesome-app.com',
    'Awesome App (contact@awesome.com)'
);

$session = new Session(
    STORE_ID,
    ACCESS_TOKEN,
    SCOPES
);

$client = new \Tiendanube\Clients\Rest($session->getStoreId(), $session->getAccessToken());
$response = $client->get('products');
var_dump($response->getStatusCode(), $response->getDecodedBody(), $response->getHeaders(), $response->getPageInfo());
```
