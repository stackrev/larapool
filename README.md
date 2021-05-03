## Larapool

### A **Laravel** package to connect all Iraninan payments gateways

---

### **Active ports** :

IDPay : [https://idpay.ir](https://idpay.ir)

---

### **Installing** :

>##### Step 1 :
> 
>>composer require mst-ghi/larapool

>##### Step 2 : Apply the following changes to the config/app.php file
>>    'providers' => [
>>      MstGhi\Larapool\LarapoolServiceProvider::class
>>    ],
> 
>>    'aliases' => [
>>      'Larapool' => MstGhi\Larapool\Larapool::class
>>    ]

>##### Step 3 : Publishing required files
> 
>>php artisan vendor:publish --provider=MstGhi\Larapool\LarapoolServiceProvider 

>##### Step 4 : Create transaction tables
> 
>>php artisan migrate


> The installation operation is complete. 
Now open the larapool.php file in the config/larapool.php and enter the settings related to your desired banking portal.

---
