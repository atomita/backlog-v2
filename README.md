Backlog-v2
==========

this is [backlog api v2](https://developer.nulab.com/ja/docs/backlog/) wrapper with API Key.



## Get started

Install the "atomita/backlog-v2" using the composer.  

```php
require {composer install dir} . "/vendor/autoload.php";

use \atomita\Backlog;
use \atomita\BacklogException;

$backlog = new Backlog('space-name', 'api-key');
try{
    $space = $backlog->space->get();
    var_dump($space);

    $comment = $backlog->issues->param('issue id')->comments->post(['content' => 'comment message']));
    var_dump($comment);
}
catch(BacklogException $e){
    // error
}
```


This is released under the LGPLv3, see LICENSE.
