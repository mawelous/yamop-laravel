# Yamop for Laravel
### Yet another MongoDB ODM for PHP as Laravel Component

Documentation for version 0.1.0

- [What's that?](#whatsthat)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Pagination](#pagination)
- [Authentication](#authentication)
- [Issues](#Issues)
- [License](#license)

<a name="whatsthat"></a>
## What's that? 
This is yet another, open source, and very simple [MongoDB](http://www.mongodb.org/) ODM for [Laravel 4](http://www.laravel.com/).
It works like the standard MongoDB PHP extension interface but returns objects instead of arrays (as ODM). Queries stay the same.
One of its coolest features are joins which allow you to query for related objects.
This version for Laravel is based on [Yamop](https://github.com/mawelous/yamop) which can be included into any PHP project. In addition to the standard features it supports Laravel based authentication.

<a name="requirements"></a>
## Requirements
+ PHP 5.3+
+ PHP MongoDB Extension
+ Laravel 4

<a name="installation"></a>
## Installation 

You can simply download it [here](https://github.com/mawelous/yamop-laravel) or use [Composer](http://getcomposer.org/).

In the `require` key inside the `composer.json` file add the following

```yml
    "mawelous/yamop-laravel": "dev-master"
```

Save it and run the Composer update command

    $ composer update

After this is done, add `mongo` in your database configuration:

```php
    'mongo' => array(
        'host'     => 'host',
        'port'     => 37847,
        'database' => 'db',
        'user'     => 'user',
        'password' => 'pass'
    ),
```

Now we need to let Laravel know about this new service provider. To do so add under `providers` in the `config\app.php` file the following:

```php
    ...
    'Illuminate\View\ViewServiceProvider',
    'Illuminate\Workbench\WorkbenchServiceProvider',
    ...
    'Mawelous\YamopLaravel\YamopLaravelServiceProvider',
```

Aliases to the Yamop classes are useful. Add them in the `aliases` array in the `config\app.php` file:

```php
    ...
    'Validator'       => 'Illuminate\Support\Facades\Validator',
    'View'            => 'Illuminate\Support\Facades\View',
    ...
    'Mapper'          => 'Mawelous\YamopLaravel\Mapper',
    'Model'           => 'Mawelous\YamopLaravel\Model',
```

To use Yamop you now just need to extend the Yamop alias `Model` from within any of your new or existing models:

```php
    class Article extends Model
    {
        protected static $_collectionName = 'articles';
    }
```

That's it!

<a name="usage"></a>
## Usage
For usage examples and further explanation take a look at the [Yamop Documentation](https://github.com/mawelous/yamop#usage). In this release for Laravel you can also use aliases for `Mapper` and `Model` which were registered during installation. See the following pagination example.

<a name="pagination"></a>
## Pagination

Yamop for Laravel supports pagination out of the box. It implements the `_createPaginator` method and extends `getPaginator`, with this you only need to pass the items per page into the method. The second parameter, the current page number, is optional.

```php
    User::getMapper()
        ->find( 'status' => [ '$ne' => User::STATUS_DELETED ] ) )
        ->sort( [ $field => $direction ] )
        ->getPaginator( $perPage );

    //or
    User::getMapper()
        ->find()
        ->getPaginator( $perPage, $currentPage );
```

<a name="authentication"></a>
## Authentication

Laravel's package of Yamop supports native like authentication.
You must first extend your `User` Model with Yamop's `Mawelous\YampoLaravel\User`

```php
    class User extends Mawelous\YamopLaravel\User
    {
        protected static $_collectionName = 'users';    
    }
```

In `auth\config.php` change the driver to `yamop`.

```php
    ...
    'driver' => 'yamop',
    ...
```

Now you can implement it as standard authentication:

```php
    class AuthController extends BaseController {
    
        public function getLogin()
        {
            return View::make( 'auth.login' );
        }
        
        public function postLogin()
        {
            if( Auth::attempt( [ 'nickname' => Input::get( 'nickname' ), 'password' => input::get( 'password' ) ] ) )
            {
                return Redirect::intended( 'dashboard' );
            } else {
                return Redirect::to( '/login' )->with( 'login_failed', true );
            }       
        }
    }
```

<a name="issues"></a>
## Issues

Any issues or questions please [report here](https://github.com/Mawelous/yamop-laravel/issues)

<a name="license"></a>
## License

Yamop is free software distributed under the terms of the [MIT license](http://opensource.org/licenses/MIT)