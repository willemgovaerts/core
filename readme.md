## Installation


```bash
composer require "levaral-dev/core"
```

## Commands

This command will generate base structure.

```bash
levaral:generate:structure
```

This command will generate all the base classes of the models. Argument {model} will generate base classes for the 
specific model 

```bash
levaral:generate:models {model?}
```

This command will generate all the [api routes/api services] in plain javascript objects

```bash
levaral:generate:apijs
```
This command will generate action class in Actions folder.

```bash
make:action {className} {?namespace}
```
for e.g
```bash
make:action GetDetail
make:action PostDetail
make:action GetDetail 'User\Profile'
```

This command will generate model and the base classes in Domain folder.

```bash
make:model {ClassName}
```

## Action Routes

Action routes can be defined in any laravel's route files like (web.php, api.php etc..), when using
action routes you don't need to define named route, it will be generated automatically.

for e.g

```bash
Action::get('get-detail', \App\Http\Actions\User\GetDetail::class); // route name User:GetDetail
Action::post('get-detail', \App\Http\Actions\User\PotDetail::class); // route name User:PotDetail
Action::post('get-detail', \App\Http\Actions\User\PotDetail::class); // route name User:PotDetail
Action::post('get-detail', \App\Http\Actions\User\Profile\GetDetail::class); // route name User:Profile:PotDetail
```

