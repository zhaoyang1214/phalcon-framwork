# phalcon多模块框架

## 安装
#### 使用composer安装
```
composer create-project phalcon-framwork/phalcon-framwork phalcon
```

## 项目介绍
#### 这是一个phalcon多模块web框架，系统封装了一些比较好用的方法
**主要特点有：**
1. 系统集成了多模块web
2. 重写封装了部分服务
3. 封装了验证器
4. 在基础控制器中封装了获取get、post、json参数并自动过滤数据
5. 对原转发（forward）做了封装


## 说明：
1、默认只有admin、home、api三个模块
要想增加模块，需要打开config/define.php，给 `MODULE_ALLOW_LIST` 增加模块名称，然后添加相应模块即可


2、 在config/define.php中修改NOW_ENV配置相应环境，支持dev、test、pro，设置后会自动启用相应的配置文件

3、 app/common/BaseController.php已封装过滤参数,过滤方法可在配置文件中配置




###### 博客
https://blog.csdn.net/u014691098/article/category/7632913
