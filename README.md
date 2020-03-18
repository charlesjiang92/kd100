### 快递100物流查询
#### 1、安装
```shell
composer require xxx/xxx
```

#### 2、调用
```php
include_once './vendor/autoload.php';
use Utils\Kd100Utils;

try {
    $kd = new Kd100Utils([]);
    $re = $kd->find("快递单号", "手机号(可选,顺丰快递需填写手机号后4位)");
    var_dump($re);
} catch (\Exception $e) {
    var_dump($e->getMessage());
}
```

#### 3、可选配置参数

- proxy - 若频繁请求，会被封IP，项目中建议使用缓存+IP代理池调用

   proxy格式：http://IP地址:端口 （如:http://127.0.0.1:1087）

- timeout - 默认 30s

```php
$kd = new Kd100Utils([
    'proxy'   => 'http://127.0.0.1:1087',
    'timeout' => 10
]);
```

