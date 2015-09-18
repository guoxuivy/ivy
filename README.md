ivy
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
[ `go run test1.go` | done: 7.6334366s ]<br>
    Ivy 666（毫秒）<br>
    YII1.3 1837（毫秒）<br>
    ThinkPHP3.1 4534（毫秒）<br>
[ D:/mygo/src/ ] # <br>
——————————————————————————<br>
测试代码：<br>

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
    //保证所有并发处理完成后回归主程序
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

