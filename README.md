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

- Sample Request : 

```
$turatel = new Turatel\TuratelController();
        $turatel->setNumbers(905549638018)
            ->setMessageBody("test mesajı")
            ->sendSms();
```
