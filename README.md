# simplePHP v7.0
## 基于OOP思想简易PHP框架。

```php
simplePHP的框架，适用于PHP大于或等于7.0
```

##架构说明
```
基本上每个类都有使用说明，详情请查看头部的注释文字。这里只介绍一些常用的方法，框架使用application管理所有类的实例化，同时编写
了一个自由函数：app(Class,Params); 它会返回一个实例对象；其中class参数为类对象，params是参数对象，可以是这样子：
$test = app(Test::class,1,2,3);
//真实代码运行方法相当于这样子：
//$test = new Test(1,2,3);

几乎所有的实例化对象都建议使用app();函数去执行，当然动态对象除外（模型对象）。

        index.php
            ↓
      Application.php
            ↓
    Application::autoder();//引用所有需要的文件
            ↓
      AutoLoader::run();//自动引入需要的文件
            ↓
  InitSystem::__construct();//初始化
            ↓
      InitSystem::boot();//初始化程序引导
            ↓
    InitSystem::register();//注册组件
            ↓
      InitSystem::run();//控制器启动

主要是这三大类:Application.php、AutoLoader.php、InitSystem.php
```

##常用的使用方法
```php
    
    1.控制器 & 路由
        只需要在目录/controller/v1/下新建一个控制器类即可,方法建议遵循ControllerInterface::class接口,并继承
        CompactController::class抽象类;如下:
        TestController.php->{
            class TestController extends CompactController implements ControllerInterface{}
        }
        其中命名方式如下:前缀 + 固定名 + 后缀
            Test = 前缀
            Controller = 对象类型
            .php = 后缀
        如果你想修改某些规律,可以在/system/http/RouteProvider.php中字段名:$controlExtra、$controlExtraFile进行修改。
        
        路由的规则跟你定义的控制器是有关联的，比如上例请求如下：
            http://www.demo.com/test -> TestController::class
            它将会调用TestController::class的方法,默认会根据请求method方法进行函数定位,如下:
                GET -> getAction()
                POST -> saveAction()
                PUT -> putAction()
                GET -> removeAction()
                
            当然,如果你不想使用restful的风格，那么你可以传入如下：
            http://www.demo.com/?ctl=test&act=add
            http://www.demo.com/test/add 这里将会调用TestController::class->add()函数
            
         关于请求参数说明：
            常规路由：
                * http://www.demo.com?ctl=test&act=add&m=wap&ver=1
                * ctl:控制器
                * act:方法函数
                * m:应用类型->框架的控制器是所有类型通用的，不通用的是模板，所以需要传入一个默认类型来区别模板的目录（views）
                * ver:控制器版本号
            
         更多例子：
          * example:
          * http://www.demo.com/Controller/Action/Id/Version/AppType(admin|web|api)
          * http://www.demo.com/Controller
          * http://www.demo.com/init/(Default:get)/(Default:0)/(Default:1)/(Default:web)
          * http://www.demo.com/api/init/1 action=>get(app:defaultRouteActionParam)
          * http://www.demo.com/api/init/get/1 action=>get:1
          * http://www.demo.com/?ctl=Controller&act=Action&ver=1
          * http://www.demo.com/?ctl=Controller&act=Action&ver=1&m=admin //admin
        
    2.模板标签
        模版引擎是基于Smarty的，它太强大了，而我们只需要少部分的功能，所以就把它精简下来.
        程序初始化的时候调用register()方法，会把app & admin 俩端的模板配置文件生成，开发时，只需要调用Helper.php:display & assign 
        方法输出模板，其中控制器抽象类CompactController::class，我已经把方法移植进去了。
        
        //标记
        标记 {} 大括号作为简单的标记
        
        //变量
        {$title} -> 变量
        {$__APP__} -> 变量
        {$Controller} -> 变量
        {$Action} -> 变量
        {$TMPL} -> 变量
        
        //逻辑
        {if $title}{/if} - > 逻辑
        {if $title eq 1}{/if} - > 逻辑
        {if $title neq 1}{/if} - > 逻辑
        {if $title == 1}{/if} - > 逻辑
        {if $title != 1}{/if} - > 逻辑
        {if $title != 1}{else}{/if} - > 逻辑
        {if $title != 1}{else}{/if} - > 逻辑
        
        //循环
        {foreach from="$data" item="item" key="key"}
            {if $key eq 'Alan'}
                {$key} - {$data}
            {else}
                {$key} - {$data}
            {/if}
        {/foreach}
        
        //调用函数
        {function name="getTime"}
        
        //调用语言函数
        {lang value="getTime"}
        
        //调用配置项
        {conf p1="app" p2="appName"}
        {conf p1="app" p2="appName"}
        
        //html中引入文件
        {include file="header.html"}
        
        //插入文件
        insert //这个用的少,更多需要自己去改写TemplateProvider::class

    3.数据库
        请自行查看DataBaseInterface::class规范
    
    4.Cookie -> CookieProvider::class
         * CookieProvider::set("key","value");
         * CookieProvider::get("key");
         * CookieProvider::exist("key","value");
         * CookieProvider::remove("key");
         * CookieProvider::destroy();
         
    5.Session -> SessionProvider::class
         * SessionProvider::set("key","val");
         * SessionProvider::get("key");
         * SessionProvider::exist("key");
         * SessionProvider::remove("key");
         * SessionProvider::destroy();
         
    6.Cache -> CacheProvider::class
         * CacheProvider::set("test4","test redis");
         * CacheProvider::exist("test4");
         * CacheProvider::get("test4");
         * CacheProvider::remove("test4");
         * CacheProvider::destroy();

    7.验证码 -> VerifyProvider::class
         * $this->getVerifyProvider()->entry();
         *
         * Verification the verify code,if is right return true else false.
         * $this->getVerifyProvider()->check($verifyCode);
     
    8.队列 -> QueueProvider::class
         * $queue->push("ids",10,5);
         * $queue->push("ids",11);
         * $queue->push("ids",["a","b","c"]);
         * $queue->index("ids");
         * $queue->size("ids");
         * $queue->shift("ids");
         * $queue->pop("ids");    
    
    9.文件操作 -> FileProvider::class
         * $file = app(FileProvider::class);
         * $file->save(ROOT_PATH."1.log","test".TIME_UTC.PHP_EOL);
         * $file->put(ROOT_PATH."1.log","test2".TIME_UTC.PHP_EOL);
         * $file->exist(ROOT_PATH."1.log");
         * $file->remove(ROOT_PATH."1.log");     
      
    10.图像处理类 -> ImageProvider::class
         * 常用的函数:
         *      makeThumb();//缩略图
         *      makeCrop();//裁剪
         *      makeWater();  //水印
    
    11.上传图片 -> UploadFileProvider::class
         * $upload = app()->getInstance(UploadFileProvider::class);
         * $res = $upload->initialize($image,"test")->save();
         *
         * dose crop the image:
         * $upload = app()->getInstance(UploadFileProvider::class);
         * $res = $upload
         *              ->setCropX(60)
         *              ->setCropY(60)
         *              ->setCropWidth(860)
         *              ->setCropHeight(456)
         *              ->initialize($image,"test")
         *              ->save();
         * return:
         * array(size = 8)
              'responseCode' => int 1
              'responseError' => string 'success.' (length=13)
              'size' => int 42
              'cropX' => int 60
              'cropY' => int 60
              'cropW' => int 860
              'cropH' => int 456
              'url' => string '/public/attachment/images/20170527/e9fef9c64cc51c5af079e3936694cea5.jpg' 
        
    12.Http请求 -> CurlProvider::class
         * $curl = app(CurlProvider::class);
         * $res = $curl->get("http://test.com/1.php");
         * $res = $curl->post("http://test.com/1.php");
         *
         * var_dump(json_decode($res,true));
    
    13.模型自动绑定
        懒病的原因一直没有研究,其实PHP也可以像java那样,对数据库读取的数据跟对象模型类进行绑定,还有就是请求跟模型对象绑定;
        但是本人太懒了.
    

```

##目录说明
```php
    /controller 控制器
        /v1 控制器版本1目录,默认目录,建议所有的前端接口都存放在此目录
        /v2 控制器版本2目录,此时请求的参数需要传入ver=2
        
    /lang 语言文件目录
        /cn 中文语言文件  
        
    /public
        /attachment 附件目录
        /cache  缓存目录
        /dbBackup   数据库备份
        /logger     日志输出
        /session    
        /sqlite sql数据库文件
        /verify    验证码
        
    /system 系统核心
        /basic  系统核心基础库
        /bacicInterface 接口类
        /config 系统配之类
        /data   实体对象类
        /database   数据库操作类
        /handler    一些处理类
        /helper 自由函数
        /http   HTTP有关的对象类
        /model  模型
        /other  图像,上传,验证码类
        /store    存储相关类
        /template   模板提供者
        /utils      工具
        
    /views html模板
        
```
