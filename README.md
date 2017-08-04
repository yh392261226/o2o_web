## 目录结构
.
```
├── framework                       框架
├── dao                             dao层
├── manage                          后台
│   ├── cache                       后台缓存
│   ├── controllers                 后台控制器
│   ├── index.php                   后台入口文件
│   └── views                       后台视图
│       ├── css                     css样式
│       ├── images                  图片
│       ├── js                      javascript
│       └── templates               模板
├── models                          公共model
├── public                          公共函数或类
├── library                         第三方库
└── web                             前台
    ├── cache
    ├── controllers
    ├── index.php
    └── views
        ├── css
        ├── images
        ├── js
        └── templates
```

## 变量声明及定义：所有命名需要英文且具有意义
变量：$user_name   小写且多个单词用下划线连接
函数: function myFunc()  多个单词采用首字母小写， 第二个单词开始，每个单词首字母大写
常量：CONST 全大写
类：驼峰式命名   MyClass() 驼峰
文件名：controller与model 文件首字母大写,多个单词采用驼峰式 如：User.php UserExt.php，
       views 文件名小写，多个单词用_连接，后缀名为：.html(,特殊情况可以直接使用.php).
文件夹：全部小写，多个单词采用_连接

