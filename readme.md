## Installation


```bash
composer require levaral-dev/core:dev-master
```

## Commands

This command will generate base structure.

```bash
levaral:structure
```

This command will generate all the base classes of the models. Argument {model} will generate base classes for the 
specific model 

```bash
levaral:models {model?}
```

This command will generate all the [api routes/api services] in plain javascript objects

```bash
levaral:api-js
```
This command will generate action class in Actions folder.

```bash
make:action {namespace}
```
for e.g
```bash
make:action 'User\Profile\GetDetail'
```

This command will generate model and the base classes in Domain folder.

```bash
make:model {namespace}
```

This command will scan all the Actions in `Api` folder and generate test classes

```bash
levaral:generate-test
```

This command will generate expo token table and model. Those tokens will be used to send
push notifications. This package also has expo push notification channel i.e `ExpoPushNotification`

```
levaral:user-expo-tokens
```

## Notification Channels

####ExpoPushNotification
add `toExpo` to your notification class for e.g
```php
function toExpo()
{
    return [
        'body' => 'Test Message'
    ];
}
```

If you include `to` in above array then the notification will be send to only that particular device, if not then it will send 
the notifications to only that notifiable object devices, which are in the `user_expo_tokens` table


## Action Routes

Action routes can be defined in any laravel route files like (web.php, api.php etc..), when using
action routes you don't need to define named route, it will be generated automatically.

for e.g

```php
Action::get('get-detail', \App\Http\Actions\User\GetDetail::class); // route name User:GetDetail
Action::post('get-detail', \App\Http\Actions\User\PostDetail::class); // route name User:PostDetail
Action::post('get-detail', \App\Http\Actions\User\PostDetail::class); // route name User:PostDetail
Action::post('get-detail', \App\Http\Actions\User\Profile\GetDetail::class); // route name User:Profile:PostDetail
```

