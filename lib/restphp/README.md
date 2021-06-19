# restphp-core

#### 介绍
RestPHP框架核心代码

#### 软件架构
轻便的插件化框架，可丰富你的项目的路由器选择


#### 安装教程

1.  使用submodule clone 本项目到您的项目的插件目录中，如：restphp

2.  配置URL重新规则，将所有请求地址重写到上一步的程序入口文件。如，Nginx重写配置：

```
location / {
    index  index.php;
    if (!-e $request_filename) {            
        rewrite ^/(.*)$ /index.php?$1 last;                
    }            
}
```

3.  项目配置文件，必要内容为：

```
//RESTPHP 相关配置
//版本号
define('REST_PHP_VERSION', '2.0');
//lib目录，此目录下的包可自动加载
define('DIR_LIB', 'lib');
//RestPHP程序目录
define('DIR_RESTPHP', DIR_LIB . DIRECTORY_SEPARATOR . 'restphp');
//路由文件生成目录
define('DIR_BUILD_TARGET', 'runtime/target');
//HTTP version
define('HTTP_VERSION', '1.1');
//默认接收报文类型
define('CONTENT_TYPE', 'application/json');
//系统时间，秒
define('SYS_TIME', time());
//系统时间，毫秒
define('SYS_MICRO_TIME', microtime(true));


//引入框架
require(DIR_RESTPHP . '/Rest.php');

//程序入口
\restphp\Rest::run();

```

4.  构建入口配置，必要内容为：

```
//版本号
define('REST_PHP_VERSION', '2.0');
//lib目录，此目录下的包可自动加载
define('DIR_LIB', 'lib');
//RestPHP程序目录
define('DIR_RESTPHP', DIR_LIB . DIRECTORY_SEPARATOR . 'restphp');
//构建时需要扫描的目录
define('DIR_BUILD', 'com');
//路由文件生成目录
define('DIR_BUILD_TARGET', 'runtime/target');

//引入框架
require(DIR_RESTPHP . '/Rest.php');

//构建程序入口
\restphp\Rest::build();

```

示例项目：https://gitee.com/sofical/dns-manager

#### 使用说明

##### 文件加载

文件支持自动加载，不需要在逻辑代码中使用require或include。其中自动加载的区域分为了两块区域。

1、项目代码区，即 DIR_BUILD 下的项目文件。

2、lib区，即DIR_LIB下的文件。lib区一般用于第三方引用插件代码。

文件加载机制是通过命名空间和类名进行自动查询匹配，因此 **类名需要和文件名保持一致** 


##### 路由编写
使用注解@RequestMapping，参数：value、method

 **value**   在class和function中都有效，值可以是一个或多，一个时，可以直接写为：value="/index.html"。多个时，应该写为：value=["/", "/index.html", "/index"]

 **method**   HTTP谓词（方法），即：GET、POST、PUT、DELETE、PATCH等，不区分大小写，建议使用大写。

 完整示例：

```
/**
 * 首页路由.
 * @RequestMapping("")
 */
class IndexController {
    /**
     * 首页.
     * @RequestMapping(value=["/", "/index.html", "/index"], method="GET")
     */
    public function index() {
        // put your logic here
        echo "hello moto!";
    }
}
```


##### 常用传参获取
###### 路径参数

路径参数使用RestHttpRequest::getPathValue()方法获取。注：多路由不支持路径参数。

访问地址/users/97，获取用户ID：97
```
/**
 * @RequestMapping("/users")
 */
class UserController {
    /**
     * @RequestMapping(value="/{userId}", method="GET")
     */
    public function userInfo() {
        $userId = RestHttpRequest::getGet("userId");
        echo $userId;
    }
}
```


###### Query参数

Query参数使用RestHttpRequest::getGet() 或 RestHttpRequest::getParameterAsObject() 获取；使用 RestHttpRequeste::getPageParam() 获取分页对象。

其中RestHttpRequest::getGet() 用于获取单个路径参数，RestHttpRequest::getParameterAsObject() 用于以对象的形式获取一个或多个参数。

1.RestHttpRequest::getGet()应用举例

访问地址/users?name=张，获取name参数值：

```
/**
 * @RequestMapping("/users")
 */
class UserController {
    /**
     * @RequestMapping(value="", method="GET")
     */
    public function userList() {
        $name = RestHttpRequest::getGet("name");
        echo $name;
    }
}
```
2.RestHttpRequest::getParameterAsObject()应用举例

访问地址/users?name=张&mobile=1360000，获取name和mobile的查询对象

```
final class queryForm {
    private $_name;
    private $_mobile;
    public function getName() {
        return $this->_name;
    }
    public function setName($name) {
        $this-_name = $name;
    }
    public function getMobile() {
        return $this->_mobile;
    }
    public function setMobile($mobile) {
        $this-_mobile = $mobile;
    }
}

/**
 * @RequestMapping("/users")
 */
class UserController {
    /**
     * @RequestMapping(value="", method="GET")
     */
    public function userList() {
        $queryForm = RestHttpRequest::getParameterAsObject(new queryForm());
        var_export($queryForm);
    }
}

```

###### Body参数
Body 参数使用 RestHttpRequest::getBody() 获取。

应用举例，获取body内容：{"username":"小红","mobile":"13800000000","age":"18"}

```
final class userForm {
    private $_name;
    private $_mobile;
    private $_age;
    public function getName() {
        return $this->_name;
    }
    public function setName($name) {
        $this-_name = $name;
    }
    public function getMobile() {
        return $this->_mobile;
    }
    public function setMobile($mobile) {
        $this-_mobile = $mobile;
    }
    public function getAge() {
        return $this->_age;
    }
    public function setAget($age) {
        $this->_age = $age;
    }
}


/**
 * @RequestMapping("/users")
 */
class UserController {
    /**
     * @RequestMapping(value="", method="POST")
     */
    public function newUser() {
        //获取为对象
        $userForm = RestHttpRequest::getBody(new userForm());
        var_export($userForm);
        //获取为数组
        $arrUser = RestHttpRequest::getBody();
        var_dump($arrUser);
    }
}
```

##### 数据验证
框架提供了以下注解表单验：

@length(min=最小长度,max=最大长度,message=错误提示)

@notnull(message=错误提示)

@mobile(message=错误提示)

@email(message=错误提示)

@domain(message=错误提示)

@date(format=日期格式,message=错误提示)

@range(min=最小长度,max=最大长度,message=错误提示)

@int(min=最小长度,max=最大长度,message=错误提示)

@ipv4(message=错误提示)

@ipv6(message=错误提示)

@inArray(value=[可选值1|可选值2],message=错误提示)

@notEmpty(message=错误提示)

@customer(method=自定义校验方法,message=错误提示)

使用示例：

    <?php
    namespace classes\controller\api\vo;
    
    /**
     * Class MessageVo
     * @package classes\controller\api\vo
     */
    class MessageVo {
        /**
         * 客户名称.
         * @length(min=1,max=20,message=名字输入长度为1~20个字符)
         * @var string.
         */
        private $_name;
    
        /**
         * 感兴趣的产品.
         * @length(min=1,max=50,message=感兴趣的产品输入长度为1-50个字符)
         * @var string
         */
        private $_product;
    
        /**
         * 手机号码.
         * @mobile(message=手机号不正确)
         * @var string
         */
        private $_mobile;
    
        /**
         * 更多说明
         * @length(max=255,message=更多说明长度不能超过255字)
         * @var string
         */
        private $_more;
    
        /**
         * @return string
         */
        public function getName()
        {
            return $this->_name;
        }
    
        /**
         * @param string $name
         */
        public function setName($name)
        {
            $this->_name = $name;
        }
    
        /**
         * @return string
         */
        public function getProduct()
        {
            return $this->_product;
        }
    
        /**
         * @param string $product
         */
        public function setProduct($product)
        {
            $this->_product = $product;
        }
    
        /**
         * @return string
         */
        public function getMobile()
        {
            return $this->_mobile;
        }
    
        /**
         * @param string $mobile
         */
        public function setMobile($mobile)
        {
            $this->_mobile = $mobile;
        }
    
        /**
         * @return string
         */
        public function getMore()
        {
            return $this->_more;
        }
    
        /**
         * @param string $more
         */
        public function setMore($more)
        {
            $this->_more = $more;
        }
    
    
    }

    <?php
    namespace classes\controller\api;
    
    use classes\controller\api\vo\MessageVo;
    use classes\service\CrmMessageService;
    use restphp\http\RestHttpRequest;
    use restphp\validate\RestValidate;
    
    /**
     * Class MessagesController
     * @RequestMapping(value="/api/messages")
     * @package classes\controller\api
     */
    class MessagesController {
        /**
         * 接收消息.
         * @RequestMapping(value="", method="POST")
         * @throws \ReflectionException
         */
        public function receiveMessage() {
            $message = RestHttpRequest::getRequestBody(new MessageVo(), true);
    
            CrmMessageService::saveMessage($message);
        }
    }


##### 数据响应

数据响应为开放自由式响应，无特殊固定规则和格式。

框架的RestHttpResponse类提供常用数据响应方法封状；RestTpl类提供了简单的模板引擎。

##### 常用方法



#### 参与贡献

1.  Fork 本仓库
2.  新建 Feat_xxx 分支
3.  提交代码
4.  新建 Pull Request
