Deneb: A simple CRUD layer for Zend_Db based models
===================================================

Deneb provides a consistent CRUD interface to using Zend_Db based models and model collections, as well as segregated read and write DB pools and selectors.  Provided are common classes for single objects, as well as collections of objects.


Single model read example:

    $user = new Deneb_User(array('username' => 'shupp'));

Single model write example:

    $user           = new Deneb_User();
    $user->username = 'shupp';
    $user->email    = 'bshupp@empowercampaigns.com';
    $user->create();

Collection model example:

    $users = new Deneb_UserCollection(array('enabled' => 1));

DB selector config example:

    db.selectors.user = "default"
    db.adapter = 'PDO_MYSQL'
    db.pools.default.write.username = "app_write"
    db.pools.default.write.password = "secret"
    db.pools.default.write.host = "1.2.3.4"
    db.pools.default.write.port = "3306"
    db.pools.default.write.dbname = "production"
    db.pools.default.read.username = "app_read"
    db.pools.default.read.password = "secret"
    db.pools.default.read.host = "5.6.7.8"
    db.pools.default.read.port = "3306"
    db.pools.default.read.dbname = "production"

And here's example direct usage, if you need it:

     $application = new Zend_Application(
         APPLICATION_ENV,
         APPLICATION_PATH . '/configs/application.ini'
     );
     $application->bootstrap();

     $selector = new Deneb_DB_Selector($application, 'user');
     $readDB   = $selector->getReadInstance();
     $writeDB  = $selector->getWriteInstance();
