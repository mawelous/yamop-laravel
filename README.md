# YamopLaravel
### Yet another MongoDB ODM for PHP as Laravel Component

- [What's that?](#whatsthat)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [getting](#getting)
    - [save, update, delete](#save)
    - [embedded objects](#embedded)
    - [relations](#related)
    - [output format](#output)
    - [pagination](#pagination)
    - [timestamps](#timestamps)
    - [dates and time](#datetime)
    - [transactions](#transactions)
    - [authentication](#authentication)
- [Issues](#Issues)
- [License](#license)

<a name="whatsthat"></a>
## What's that? 
This is yet another, open source, and very simple [MongoDB](http://www.mongodb.org/) ODM  for [Laravel4](http://laravel.com/).
It works like the standard MongoDB PHP extension interface but returns objects instead of arrays (as ODM). Queries stays the same.
One of the coolest things are joins which allow you to query for related objects.
Laravel's version is based on standard [Yamop](https://github.com/mawelous/yamop) which can be applied to any PHP piece of code. In addition it supports authentication.
This documentation covers all Yamop features.

List of features:

- [String IDs](#stringid) (easier linking in views)
- [Embedded objects](#embedded)
- [Related objects](#related) (performing "join like" operations)
- [JSON format](#output)
- [Paginator](#pagination)
- [Timestamps](#timestamps) (created_at and updated_at fields added on demand)
- [Printing date and time](#datetime)
- [Transactions](#transactions) (PHP error support only)
- [Authentication](#authentication)

<a name="requirements"></a>
## Requirements
+ PHP 5.3+
+ PHP MongoDB Extension

<a name="installation"></a>
## Installation 

You can simply download it [here](https://github.com/mawelous/yamop-laravel) or use [Composer](http://getcomposer.org/).

In the `require` key of `composer.json` file add the following

```yml
    "mawelous/yamop-laravel": "dev-master"
```

Run the Composer update command

    $ composer update

Now let Yamop know your credentials. Add `mongo` in your database configuration
```php
    'mongo' => array(
        'host'     => 'host',
        'port'     => 37847,
        'database' => 'db',
        'user'     => 'user',
        'password' => 'pass'            
    ),  
```

After it's done inform Laravel about new provider. In `config\app.php` file in `providers` array add `YampoLaravelServiceProvider`
```php
    ...
    'Illuminate\View\ViewServiceProvider',
    'Illuminate\Workbench\WorkbenchServiceProvider',
    ...     
    'Mawelous\YamopLaravel\YamopLaravelServiceProvider',
```

Aliases to Yamop classes are useful. Add them in `aliases` array in `config\app.php` file
```php
    ...
    'Validator'       => 'Illuminate\Support\Facades\Validator',
    'View'            => 'Illuminate\Support\Facades\View',
    ...     
    'Mapper'          => 'Mawelous\YamopLaravel\Mapper',
    'Model'           => 'Mawelous\YamopLaravel\Model',

```

Now extend `Model` from within any of your models:

```php
    class Article extends Model
    {
        protected static $_collectionName = 'articles';    
    }
```

That's it!

<a name="usage"></a>
##Usage

Each object has an `_id`, which is a `MongoId`, and an `id` key which is its string representation.

Every document in `MongoDB` is returned as an object, every key is a property - here a sample document inside `MongoDB`

```json
     {
       "_id": ObjectId("51b6ea4fb7846c9410000001"),
       "name": "John Doe",
       "birthdate": ISODate("2013-05-25T12:15:25.0Z"),
       "email": "john@something.com"
    }    
```
The document above would be represented in PHP as follows:

```php
    object(User)[44]
      public '_id' => 
        object(MongoId)[46]
          public '$id' => string '51b6ea4fb7846c9410000001' (length=24)
      public 'name' => string 'John Doe' (length=8)
      public 'birthdate' => 
        object(MongoDate)[47]
          public 'sec' => int 1369484125
          public 'usec' => int 0
      public 'email' => string 'john@something.com' (length=18)
      public 'id' => string '51b6ea4fb7846c9410000001' (length=24)
```

<a name="getting"></a>
### Getting data
Want to get a document by its id? There is a simple way.

<a name="stringid"></a>
```php
    $stringId = ;
    $user = User::findById( '51a61930b7846c400f000002' )
    //or
    $mongoId = new MongoId( '51a61930b7846c400f000002' );
    $user = User::findById( $mongoId )
```
#### Introducing Mapper
There is a `Mapper` class in Yamop which is responsible for retrieving data. I separated it from `Model` so it can stay as data container. If you want to create more complicated queries you want to use the mapper. You can get it by using the `getMapper` method or creating new instance of it passing model class as string.

```php
    //first possibility
    $mapper = User::getMapper();
    //second possibility
    $mapper = new Mapper( 'User' );
```

#### Find methods

`findOne` works exactly like native [`findOne`](#http://php.net/manual/en/mongocollection.findone.php) but it returns an object. As second parameter you can pass an array of fields. This means the parameters and queries stay the same, which is pretty great!

`find` also works like native [`find`](#http://www.php.net/manual/en/mongocollection.find.php) but it returns a `Mapper`. You can then perform other operations on it like `sort`, `limit`, `skip` which all work like native as well.
To get result as array of objects use `get` method.

```php
    $messages = Message::getMapper()
        ->find( array( 'to_id' => new mongoId( $stringId ), 'to_status' => Message::STATUS_UNREAD ) )
        ->sort( array( 'created_at' => -1 ) )
        ->limit( 10 )
        ->get(); 
```

<a name="save"></a>
### Save, Update and Delete
`save` method is used to create and update objects. That's the code to create new object and write it to the database

```php
    // properties as array
    $user = new User( array( 'name' => 'John', 'email' => 'email@email.com' ) );
    
    // or each property separately
    $user = new User;
    $user->name = 'John';
    $user->emial = 'email@email.com';
    
    $user->save();
```
You can get `_id` of newly created object just after `save`.

Deleting is simple

```php
    $user->remove();
```

Those methods return the same results as the native `remove` and `save` methods. If you want to update multiple documents use the native function like [here](#multiple-update).

### Extending Mapper
You can extend `Mapper` if you want to add more methods. For example I created UserMapper with has a method that posts a message on an user's Facebook wall. Just let it know which model class to use.

```php
class UserMapper extends Mapper
{   
    protected $_modelClassName = 'User';    
    
    public function findActiveUsers( $limit = 10, $sort = 'birthdate' )
    {
        //method code
    }    
}    
```

If you want to register a different `Mapper` for a model just declare it in the model

```php
class User extends Model
{
    ...
    protected static $_mapperClassName = 'UserMapper';
    ...
```

Now you just execute the `Mapper`

```php
    $mapper = User::getMapper();
```

This will return an instance of UserMapper. You can also just create a new mapper

```php
    $userMapper = new UserMapper; 
```

<a name="multiple-update"></a>
### Count, Indexes, and multi update

All methods called on `Mapper` that are not present are passed to the original [`MongoCollection`](#http://php.net/manual/en/class.mongocollection.php). So you can use `update`, `count`, and `ensureIndex` directly with the native methods.

```php
    //count
    Message::getMapper()->count( array( 'to_id' => $userId, 'to_status' => Message::STATUS_UNREAD ) );
    //update
    Contest::getMapper()->update(
            array('status' => Contest::STATUS_READY_DRAFT,
                  'start_date' => array ('$lte' => new MongoDate(strtotime('midnight')) )),
            array('$set' => array( 'status' => Contest::STATUS_ACTIVE) ),
            array('multiple' => true)
        );
```

<a name="embedded"></a>
### Embedded objects

Do you have more objects within the current object? Yamop will convert it automatically. Just let it know.

```php
class User extends Model
{
    protected static $_collectionName = 'users';
    protected static $_mapperClassName = 'UserMapper';  
    // One Address object embedded in address property
    protected static $_embeddedObject = array (
            'address' => 'Address',
    );
    // Many Notification objects embedded in array that is kept ass notifications
    protected static $_embeddedObjectList = array (
        'notifications' => 'Notification',
    );
```

<a name="related"></a>
### Related objects

If there are relations between objects (there are sometimes) and you would like to "join" them, it's simpler than you would expect, even with `MongoDB`.

You don't have to register it anywhere. In my opinion it's better to do this explicit and avoid queries in background. 

Here's the magic:

#### One

The `joinOne` method in every `Model` takes three parameters. First is the name of the property which keeps the `MongoId` of the related object, second is the related objects class, and third is the property name it will be joined at.

```php
    $user = User::findById( new MongoId( $stringId ) )->joinOne( 'contest_id', 'Contest', 'contest')
    // and there it is
    $contest = $user->contest;
```

#### Many

The `joinMany` method in every `Model` has also three parameters. First is the name of the property which keeps an array of `MongoId`'s, second is the related objects class, and third is the property name it will be joined at.

```php
    $user = User::findById( new MongoId( $stringId ) )->joinMany( 'contests', 'Contest', 'contests')
    // and you have array of contests there
    $contests = $user->contests;
```

If you want to join items to a list of items use `join` in a `Mapper`. Three parameters as in `joinOne`.

```php
    $commentsList = Comment::getMapper()
        ->find( array( 'contest_id' => new MongoId( $contestId ) ) )
        ->join( 'user_id', 'User', 'author' )
        ->limit( 10 )
        ->get();
```

<a name="output"></a>
### Output format

Default fetching mode converts arrays to objects but you can also get array or JSON with `getArray` and `getJson`.

```php
    //first possibility
    Comment::getMapper()
        ->find( array( 'contest_id' => new MongoId( $contestId ) ) )
        ->getArray();
        
    Comment::getMapper()
        ->find( array( 'contest_id' => new MongoId( $contestId ) ) )
        ->getJson();
    
    /* second possibility
        three fetch types as constants in Mapper
        FETCH_OBJECT
        FETCH_ARRAY 
        FETCH_JSON  
    */
    Comment::getMapper( \Mawelous\Yamop\Mapper::FETCH_JSON )
        ->find( array( 'contest_id' => new MongoId( $contestId ) ) )
        ->get();
        
    /* third possibility */
    Comment::getMapper()
        ->setFetchType(\Mawelous\Yamop\Mapper::FETCH_JSON )
        ->find( array( 'contest_id' => new MongoId( $contestId ) ) )
        ->get();        
```

<a name="pagination"></a>
### Pagination

Yamop supports pagination out of the box.

```php
    User::getMapper()
        ->find( 'status' => array ( '$ne' => User::STATUS_DELETED )) )
        ->sort( array( $field => $direction ) )
        ->getPaginator( $perPage );
```

<a name="timestamps"></a>
### Timestamps

It's common to have a `created_at` and `updated_at` key in our objects. If you want to have them be set automatically for your `Model`, just declare it:

```php
class User extends Model
{
    ...
    public static $timestamps = true;   
    ....
```

<a name="datetime"></a>
### Printing date and time

Whether you have a timestamp or not, you might still like to print the date or time. It's recommend to keep dates as `MongoDate` this way you can echo it with `getTime` or `getDate` which takes two parameters. First is the `MongoDate` property name, second is a string that represents format passed to the PHP `date` function:

```php
    //date as string
    $user->getDate( 'birthdate', 'Y/m/d' );
    //time as string
    $user->getTime( 'created_at', 'Y/m/d H:i');
    //time as string using default format set in $dateFormat
    $user->getTime( 'created_at' );    
```

`Mawelous\Yamop\Model` has its default date format defined in the public static `$dateFormat` property and a time format in `$timeFormat`. You can override it if you like.

<a name="transactions"></a>
### Transactions

**EXPERIMENTAL!** - It's an addition to Yamop which works independently. It doesn't support a [two phase commit](#http://docs.mongodb.org/manual/tutorial/perform-two-phase-commits/) but at least it can revert changes.

That's what `Mawelous\Yamop\Transaction` is for. First you have to handle errors and run the `rollback` method within it. 

Similar to this:

```php
    set_error_handler( function($code, $error, $file, $line) {
        Transaction::rollback();
        require_once path('sys').'error'.EXT;
        Laravel\Error::native($code, $error, $file, $line);
    });
```

Then you can start using the `add` method. With `add` you add code to revert changes you made with save or update. You can use a closure to do that. 

Here an example:

```php
    User::getMapper()->update(
        array('_id' => array ( '$in' => $userIds )),
        array('$inc' => array ('active_contests' => -1 )),
        array('multiple' => true)
    );
    
    Transaction::add( function () use ( $userIds ) {
        User::getMapper()->update(
            array('_id' => array ( '$in' => $userIds )),
            array('$inc' => array ('active_contests' => 1 )),
            array('multiple' => true)
            );
    });
```
Now when error happens `rollback` will invoke all added methods.

<a name="authentication"></a>
### Authentication

Laravel's package of Yamop supports authentication.
First extend your `User` with Yamop's `Mawelous\YampoLaravel\User`
```php
    class User extends Mawelous\YamopLaravel\User
    {
        protected static $_collectionName = 'users';    
    }
```
In `auth\config.php` change driver to `yamop`.
```php
    ...
    'driver' => 'yamop',
    ...
```

It's ready! Now you can implement it like standard authentication. For example
```php
    class AuthController extends BaseController {
    
        public function getLogin()
        {
            return View::make( 'auth.login' );
        }
        
        public function postLogin()
        {
            if (Auth::attempt( array( 'nickname' => Input::get( 'nickname' ),
                                      'password' => input::get( 'password' ))))
            {
                return Redirect::intended( 'dashboard' );
            } else {
                return Redirect::to('/')->with( 'login_failed', true );
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