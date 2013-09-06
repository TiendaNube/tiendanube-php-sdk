Tienda Nube/Nuvem Shop SDK for PHP
==================================

This SDK provides a simplified access to the [Tienda Nube/Nuvem Shop API](https://github.com/TiendaNube/api-docs).

Installation
------------
This SDK is mounted on top of [Requests for PHP](https://github.com/rmccue/Requests), so we recommend using [Composer](https://github.com/composer/composer) for installing.

Simply add the `tiendanube/php-sdk` requirement to composer.json. You will also need to set minimum-stability to dev.

```json
{
    "require": {
        "tiendanube/php-sdk": "1.*"
    },
    "minimum-stability": "dev"
}
```

Then run `composer install` or `composer update` to complete the installation.

If you need an autoloader, you can use the one provided by Composer:

```php
require 'vendor/autoload.php';
```


Authenticating Your App
-----------------------
When a user installs your app, he will be taken to your specified Redirect URI with a parameter called `code` containing your temporary authorization code.

With this code you can request a permanent access token.

```php
$code = $_GET['code'];

$auth = new TiendaNube\Auth(CLIENT_ID, CLIENT_SECRET);
$store_info = $auth->request_access_token($code);
```

The returned value will contain the id of the authenticated store, as well as the access token and the authorized scopes.

```php
var_dump($store_info);
//array (size=3)
//  'store_id' => string '1234' (length=4)
//  'access_token' => string 'a2b544066ee78926bd0dfc8d7bd784e2e016b422' (length=40)
//  'scope' => string 'read_products,read_orders,read_customers' (length=40)
```

Keep in mind that future visits to your app will not go through the Redirect URI, so you should store the store id in a session.

However, if you need to authenticate a user that has already installed your app (or invite them to install it), you can redirect them to login to the Tienda Nube/Nuvem Shop site.

```php
$auth = new TiendaNube\Auth(CLIENT_ID, CLIENT_SECRET);

//You can use one of these to obtain a url to login to your app
$url = $auth->login_url_brazil();
$url = $auth->login_url_spanish();

//Redirect to $url
```

After the user has logged in, he will be taken to your specified Redirect URI with a new authorization code. You can use this code to request a new request token.


Making a Request
----------------
The first step is to instantiate the `API` class with a store id and an access token, as well as a [user agent to identify your app](https://github.com/TiendaNube/api-docs#identify-your-app). Then you can use the `get`, `post`, `put` and `delete` methods.

```php
$api = new TiendaNube\API(STORE_ID, ACCESS_TOKEN, 'Awesome App (contact@awesome.com)');
$response = $api->get("products");
var_dump($response->body);
```

You can access the headers of the response via `$response->headers` as if it were an array:

```php
var_dump(isset($response->headers['X-Total-Count']));
//boolean true

var_dump($response->headers['X-Total-Count']);
//string '48' (length=2)
```

For convenience, the `X-Main-Language` header can be obtained from `$response->main_language`:

```php
$response = $api->get("products/123456");
$language = $response->main_language;
var_dump($response->body->name->$language);
```

Other examples:

```php
//Create a product
$response = $api->post("products", [
    'name' => 'Tienda Nube',
]);
$product_id = $response->body->id;

//Change its name
$response = $api->put("products/$product_id", [
    'name' => 'Nuvem Shop',
]);

//And delete it
$response = $api->delete("products/$product_id");

//You can also send arguments to GET requests
$response = $api->get("orders", [
    'since_id' => 10000,
]);
```

For list results you can use the `next`, `prev`, `first` and `last` methods to retrieve the corresponding page as a new response object.

```php
$response = $api->get('products');
while($response != null){
    foreach($response->body as $product){
        var_dump($product->id);
    }
    $response = $response->next();
}
```

Exceptions
----------
Calls to `Auth` may throw a `Tiendanube\Auth\Exception`:

```php
try{
    $auth->request_access_token($code);
} catch(Tiendanube\Auth\Exception $e){
    var_dump($e->getMessage());
    //string '[invalid_grant] The authorization code has expired' (length=50)
}
```

Likewise, calls to `API` may throw a `Tiendanube\API\Exception`. You can retrieve the original response from these exceptions:

```php
try{
    $api->get('products');
} catch(Tiendanube\API\Exception $e){
    var_dump($e->getMessage());
    //string 'Returned with status code 401: Invalid access token' (length=43)
    
    var_dump($e->response->body);
    //object(stdClass)[165]
    //  public 'code' => int 401
    //  public 'message' => string 'Unauthorized' (length=12)
    //  public 'description' => string 'Invalid access token' (length=20)
}
```
