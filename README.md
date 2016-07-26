ivy(test1)
===
a PHP micro-framework

闲暇时间写的一款mini框架

采用命名空间 自动加载mvc文件 ，自动适配分组模式。

支持ActiveRecord方式的ORM数据库对象持久化，

model提供'table','distinct','field','join','where','group','having','union','order','limit','page'连贯操作。

支持多种数据库切换，改写配置文件即可。

支持多缓存方式，默认memcache、且提供集群方式缓存。

提供可扩展的日志系统，默认文件日志方式。

项目本身就是一个demo，核心框架文件夹为framework文件夹。

————————————————————————<br>
以下为简单的性能对比测试结果：<br>
3框架仅使用控制器+模版<br>
运行耗时单位（毫秒）<br>
```Go
[ `go run test1.go` | done: 7.6334366s ]
    Ivy （666毫秒）
    YII1.3 （1837毫秒）
    ThinkPHP3.1 （4534毫秒）
[ D:/mygo/src/ ] # 
```
——————————————————————————<br>
测试代码：<br>
```Go
package main

import (
    "fmt"
    "io/ioutil"
    "net/http"
    "time"
)

//通用内容读取
func Get(url string) (content string, statusCode int) {
    resp, err1 := http.Get(url)
    if err1 != nil {
        statusCode = -100
        return
    }
    defer resp.Body.Close()
    data, err2 := ioutil.ReadAll(resp.Body)
    if err2 != nil {
        statusCode = -200
        return
    }
    statusCode = resp.StatusCode
    content = string(data)
    return
}

//日志单例end

func test_f(url string) {
    //时间戳
    t1 := time.Now().UnixNano()
   
    t := ""
    for i := 0; i < 100; i++ {
        content, _ := Get(url)
        t = content
    }
    t2 := time.Now().UnixNano() - t1
    fmt.Println(t, t2/1000000) //毫秒
}

func main() {
    test_f("http://localhost/beauty_admin/index.php?r=index/login1")
    test_f("http://localhost/beauty/index.php?r=site/test")
    test_f("http://localhost/HJRCMS/index.php/index/test")
}
```



__________________简单教程____________________

单一入口文件<br>
<?php<br>
//如果框架在其他目录则需要自定义__ROOT__常量<br>
defined('__ROOT__') or define('__ROOT__', dirname(__FILE__));<br>
$ivy=dirname(__DIR__).'/ivyFramework/framework/Ivy.php';<br>
require_once($ivy);<br>
//defined('IVY_DEBUG') or define('IVY_DEBUG',true);<br>
//error_reporting(E_ALL & ~E_NOTICE);<br>
$app = Ivy::createApplication()->run();<br>


URL示例<br>
http://www.test.com/index.php?r=admin/index/index<br>
参数r为路由规则 智能适配分组模式  分组名/控制器/方法 或者 根控制器/方法名<br>

项目目录结构请参看<br>
https://github.com/guoxuivy/veecar<br>
