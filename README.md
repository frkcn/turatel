# turatel laravel package

About
====================

Just sending single sms using turatel sms service.

Usage
====================

- Composer : 
```
"frkcn/turatel" : "dev-master"
```
- App.php include : 
``` 
Frkcn\Turatel\TuratelServiceProvider::class,
```
- Config Publish : 
```
php artisan vendor:publish 
```

- Config setting  : 
```
Config/turatel.php username, password etc.
```

- Include Once Before Using :

```
use Frkcn\Turatel\TuratelController;
```

- Sample Request For Single Number : 


```
$turatel = new TuratelController();
$turatel->setNumbers("5XXXXXXXXX")
        ->setMessageBody("test message")
        ->sendSms();
```

- Sample Request For Multiple Numbers : 

```
$turatel = new TuratelController();
$turatel->setNumbers("5XXXXXXXXX,5XXXXXXXXX")
        ->setMessageBody("test message")
        ->sendSms();
```
