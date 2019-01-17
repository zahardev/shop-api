# Symfony 4 Shop REST API
Symfony 4 startpoint for the shop REST API.
 
By default, system contains 2 roles: "Admin" and "Cash Register" (Check "Usage" section).
It supports HATEOAS (HAL) technology, so, API structure is self-explained, and you can navigate between entry points. 


## Installation
1. Clone repository:
   ```sh
   git clone https://github.com/zahardoc/shop-api.git
   ```
1. Create mysql database and user.
   ```bash
   mysql -u root -p
   ``` 
   
   ```mysql
   CREATE USER 'newuser'@'localhost' IDENTIFIED BY 'password';
   CREATE DATABASE dbname;
   GRANT ALL PRIVILEGES ON dbname.* to 'myuser'@'localhost';
   ```

1. Create .env.local file and change environment to your needs.
   (Optional) Change environment variable:
   ```dotenv
   APP_ENV=production
   ```

   Provide database credentials:
   ```dotenv
   DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name
   ```

1. (Optional) Create .env.test.local file and provide credentials to the test database.

1. Install dependencies:
   ```bash
   composer install
   ```
   
1. Generate JWT keys:
   ```bash
   mkdir config/jwt
   openssl genrsa -out config/jwt/private.pem -aes256 4096
   openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
   ```
   For the password, use JWT_PASSPHRASE value from the .env file
   
1. Make migrations:
    ```bash
    bin/console doctrine:migrations:migrate
    ```
     
1. (Optional) Load fixtures:
   ```bash
   bin/console hautelook:fixtures:load -n
   ```


## Usage


### Authentication:
First of all, you need to obtain token to be able to send requests to the server:

```php
$client = new GuzzleHttp\Client(['base_uri' => 'http://pepijn-shop.loc']);
$credentials = [
    'username' => '%username%',
    'password' => '%password%',
];
$response = $client->post('/token', [GuzzleHttp\RequestOptions::JSON => $credentials]);
$data = json_decode($response->getBody(), true);
$token = $data['token'];
```
Then, you'll need to add header 
```http request
Authorization: Bearer %token%
```
to all your requests.


### Actions

For now, system supports such actions:

#### Admin: Add a product (properties: barcode, name, cost, vat-class (6% or 21%))

```php
$product = [
    'name' => 'Test Product',
    'barcode' => 9999999999999,
    'cost' => 19.75,
    'vatClass' => 6,
];

$response = $client->post('/products', [
    'json' => $product,
    'headers' => ['Authorization' => 'Bearer '.$token],
]);

```

#### Admin: List all products
```php
$response = $client->get('/products', [
    'headers' => ['Authorization' => 'Bearer '.$token],
]);
```

#### Cash register: Get a product by barcode
```php
$response = $client->get('/products/9999999999999', [
    'headers' => ['Authorization' => 'Bearer '.$token],
]);
```


#### Cash register: Create a new receipt
```php
$response = $client->post('/receipts', [
    'headers' => ['Authorization' => 'Bearer '.$token],
]);
```

#### Cash register: Add a product by barcode to the receipt
```php
$response = $client->patch('/receipts/3f2e511d-f775-4324-9c38-17b93d8a55b0', [
    'json' => [
        'op' => 'add',
        'path' => '/items',
        'value' => [
            'barcode' => '1111111111111',
            'quantity' => 3,
        ],
    ],
    'headers' => ['Authorization' => 'Bearer '.$token],
]);
```


#### Cash register: Change the amount of the last product on the receipt
```php
$response = $client->patch('/receipts/7be3393b-3764-4f42-bf9b-f5b28a3f7c85', [
    'json' => [
        'op' => 'replace',
        'path' => '/items/last/quantity',
        'value' => 5,
    ],
    'headers' => ['Authorization' => 'Bearer '.$token],
]);
```


#### Cash register: Finish a receipt
```php
$response = $client->patch('/receipts/7be3393b-3764-4f42-bf9b-f5b28a3f7c85', [
    'json' => [
        'op' => 'replace',
        'path' => '/status',
        'value' => 'finished',
    ],
    'headers' => ['Authorization' => 'Bearer '.$token],
]);
```


#### Cash register: Get the receipt, including all product names grouped, amount of that product, costs per row and total, and total vat per class
```php
$response = $client->get('/receipts/7be3393b-3764-4f42-bf9b-f5b28a3f7c85', [
    'headers' => ['Authorization' => 'Bearer '.$token],
]);
```

