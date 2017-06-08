<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/5/19 0019
 * Time: 22:22
 */

return [
    'app' => [
        'appName' => 'simplePHP',
        'versionCode' => 1,
        'versionName' => 'v1.2',
        'siteDomain' => '',
        'isDebug' => true,
        'defaultTimeZone' => 'PRC',
        'locale' => 'cn',
        'imageMaxFileSize' => 3096,//Kb
        'authToken' => '92463dd8ebcb6637dd606ddd3c2eff16',


        /**
         * http route rule.
         *
         * The Controller must be before of Action input.And the appType is optional input.
         * And ID must be before of version input.Because the program is check the id first.Then check the Version.
         * ID & Version must be type of int,The program will change it from string to int.
         * And Version final changed to 'Controller\version#1' or 'Controller\version#2'....
         *
         * example:
         * http://www.demo.com/Controller/Action/Id/Version/AppType(admin|web|api)
         * http://www.demo.com/Controller
         * http://www.demo.com/init/(Default:get)/(Default:0)/(Default:1)/(Default:web)
         * http://www.demo.com/api/init/1 action=>get(app:defaultRouteActionParam)
         * http://www.demo.com/api/init/get/1 action=>get:1
         * http://www.demo.com/?c=Controller&a=Action&ver=1
         * http://www.demo.com/?c=Controller&a=Action&ver=1&appType=admin //admin
         *
         *
         */
        'appType' => [
            'admin',
            'app',
            'api'
        ],
        'defaultAppType' => "app",
        'defaultControllerVersion' => 1,

        /**
         * The Controller weill changed to InitController.
         */
        'defaultRouteController' => 'Init',

        /**
         * The Action weill changed to getAction.
         */
        'defaultRouteActionParam' => 'get',
        'defaultRouteControllerParam' => 'c',
        'defaultRouteActionParam' => 'a',
        'defaultRouteIdParam' => 'id',
        'defaultRouteVersionParam' => 'ver',
        'defaultRouteAppTypeParam' => 'appType',
    ],
    'session' => [
        'sessionExpire' => 3600 * 8,
        'sessionPath' =>'public/session/'

    ],
    'cookies' => [
        'cookieExpire' => 3600,
        'cookieDomain'  => '',
        'cookiePath'  =>  '/'
    ],
    'db' => [
        'default' => 'mysql',
        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'test',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'prefix' => 'lx_'
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => 'forge',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'prefix' => 'lx_',
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => 'public/sqlite/database.sqlite',
            'prefix' => '',
        ],

    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'expire' => 12 * 3600,
    ],
    'cache' => [
        'default' => 'file',
        'enabled' => true,
        'file' => [
            'driver' => 'file',
            'path' => 'public/cache/data/',
            'expire' => 12 * 3600,
        ],
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => '',
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT  => 2000,
            ],
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
            'expire' => 12 * 3600,
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
    ],
    'mail' => [
        'host' => '',
        'smtp' => '',
        'port' => 25,
        'from' => [
            'address' => '',
            'name' => '',
        ],
        'username' => '',
        'password' => '',
    ],
    'queue' => [
        'default' => 'sync',
        'sync' => [
            'driver' => 'sync',
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'retry_after' => 90,
        ],
    ],
    'storage' => [
        'attachment' => 'public/attachment/',
        'attachmentImages' => 'public/attachment/images/',
        'attachmentFiles' => 'public/attachment/files/',
        'dbBackup' => 'public/dbBackup/',
        'logger' => 'public/logger/',
        'cache' => 'public/cache/',
        'cacheData' => 'public/cache/data',
        'cacheAdmin' => 'public/cache/admin',
        'cacheAdminTpl' => 'public/cache/admin/tpl',
        'cacheAdminTplCache' => 'public/cache/admin/tpl/cache',
        'cacheAdminTplCompiled' => 'public/cache/admin/tpl/compiled',
        'cacheApp' => 'public/cache/app',
        'cacheAppTpl' => 'public/cache/app/tpl',
        'cacheAppTplCache' => 'public/cache/app/tpl/cache',
        'cacheAppTplCompiled' => 'public/cache/app/tpl/compiled',
        'cacheDb' => 'public/cache/db',
        'sqlite' => 'public/sqlite/',
        'session' => 'public/session/',
    ]
];