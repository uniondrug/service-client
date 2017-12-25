# uniondrug service client

> UnionDrug微服务`MicroService`客户端`consumer`。

* PHP `7.1+`
* Phalcon `3.2+`


### methods

1. Restful请求服务
    1. `delete`(`string`, `string`, `array`, `array`)
    1. `get`(`string`, `string`, `array`)
    1. `head`(`string`, `string`, `array`)
    1. `options`(`string`, `string`, `array`, `array`)
    1. `patch`(`string`, `string`, `array`, `array`)
    1. `post`(`string`, `string`, `array`, `array`)
    1. `put`(`string`, `string`, `array`, `array`)
1. Response结果返回
    1. `withError`(`string`, `int`)
    1. `withList`(`array`)
    1. `withObject`(`array`)
    1. `withPaging`(`array`, `ResponsePaging`)
    1. `withSuccess`()


```php
public function postAction(){
    $name = 'serviceName';
    $route = 'route/action';
    $query = ["page" => 1];
    $body = ["userId" => 1, "options" => ["key" => "value"]];
    $this->serviceClient->post($name, $route, $query, $body);
}
```



*Directory*

```text
└── vendor
    └── uniondrug
        └── service-client
            ├── src
            │   └── Client.php
            └── README.md
```

*Composer*

```json
{
    "autoload" : {
        "psr-4" : {
            "UniondrugServiceClient\\" : "vendor/uniondrug/service-client/src"
        }
    }
}
```