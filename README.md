## Larapool

### A **Laravel** package to connect all Iraninan payments gateways

---

### **Active ports** :

- [x] IDPay
- [ ] Mellat
- [ ] Sadad
- [ ] Zarinpal
- [ ] Payline
- [ ] Jahanpay
- [ ] Saderat
- [ ] IranKish
- [ ] Saman
- [ ] Parsian
- [ ] Pay
- [ ] JiBit
- [ ] AP
- [ ] BitPay

---

### **Installing** :

##### Step 1 :

```php
composer require mst-ghi/larapool
```

##### Step 2 : Apply the following changes to the config/app.php file

```php
   'providers' => [
         MstGhi\Larapool\LarapoolServiceProvider::class
   ],

  'aliases' => [
        'Larapool' => MstGhi\Larapool\Larapool::class
   ]
```

##### Step 3 : Publishing required files

```php
php artisan vendor:publish --provider=MstGhi\Larapool\LarapoolServiceProvider 
```

##### Step 4 : Create transaction tables

```php
php artisan migrate
```

---

### Package configs

>The installation operation is complete. Now open the larapool.php file in the **config/larapool.php** and enter the
>settings related to your desired banking portal.

---

### Create new transaction

> Your project **Order** model must use the **TransactionAble** trait. This trait adds relation **transactions()** to order model <br>
> You have to register the order and then create a transaction for that order

```php
$order = Order::create([
    'paid_price' => 1000
    // your data
]);

$resId = \MstGhi\Larapool\LarapoolTransaction::generateResId();

// create new transaction
$order->transactions()->create([
    'user_id' => $user->id, // customer id here, this is optional
    'port_id' => \MstGhi\Larapool\Larapool::P_IDPAY,
    'price' => $order->paid_price,
    'res_id' => $resId,
    'platform' => \MstGhi\Larapool\Larapool::PLATFORM_WEB,
    'last_change_date' => strtotime(now()),
]);

// In the next step, transfer to the bank portal using resId
```

>In the next step, transfer to the bank portal using **$resId**

---

### Redirect to bank

> Can be redirected directly to the bank portal using the **redirect()** <br>
> or<br>
> Received the relevant link and connected to the bank portal in other ways using the **redirectLink()** <br>
>> The choice of redirect method to the bank depends on your project
```php
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use MstGhi\Larapool\LarapoolTransaction;
use MstGhi\Larapool\Larapool;
use MstGhi\Larapool\IDPay\IDPay;
use MstGhi\Larapool\Exceptions\NotFoundTransactionException;

public function redirect(Request $request, $resId)
{
    $exception = null;
    
    $transaction = LarapoolTransaction::with(['transactionable'])->where('res_id', $resId)->first();
    
    if (!$transaction) {
    
        $exception = new NotFoundTransactionException();
        
    } else {
    
        /** @var IDPay $larapool */
        $larapool = new Larapool(Larapool::P_IDPAY);
        
        try {
        
            $refId = $larapool->set(1000)->ready()->refId();
            // any operation on $refId here ...
        
            /**
            * redirect by php header() method automatically 
            */
            $larapool->redirect();
            
            // Or ...
            
            /**
            * relevant link and connected to the bank portal in other ways
            */
            $link = $larapool->redirectLink();
            
            return response()->json([ 'link' => $link ], Response::HTTP_OK);
            
        } catch (Exception $e) {
        
            $exception = $e;
            
        }
    }
        
    if ($exception) {
    
        Log::critical(
            "Error in getting payment url: {$exception->getMessage()} #transactionId: {$transaction->id}"
        );
        
        throw $exception;
    }
    
}

```

---
