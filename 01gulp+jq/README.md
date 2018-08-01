# 电竞数据库pc端（兼容ie8）

## 项目预备 

1. 安装node(v4.0.0及以上)
2. 安装依赖``npm install``
3. 生成页面``gulp gen --page {pageName}``
4. 开发``npm run dev``

## 项目目录
<pre>
|-- cover   html文件夹
|
|-- src 
|   \-- less less文件编写位置
|   \-- script js脚本
|       \-- js 页面入口文件放在这里
|       \-- lib 其他官方js
|
|-- dist 正式环境输出文件夹
|
|-- pre 预发布环境输出文件夹
|
|-- tmp 执行npm run dev时，输出的文件夹
</pre>

## 代码规范
0. js使用AMD规范。使用requirejs作为模块加载器。
1. js代码不可使用es6版本编写。使用es5，同时做到兼容ie8。
2. js命名使用驼峰时命名方式，js使用eslint进行检查
3. 样式使用less作为开发，[规范](https://github.com/fex-team/styleguide/blob/master/css.md)。允许使用css3，但是需要做到降级处理。
    比如ie8下可以正常展示。
4. less文件中，注释使用``/* 这里是注释 */``
5. 文件命名方式，使用破折线的方式

## 开发规范
1. 项目使用git进行代码管理
2. 每个人在自己的分支（自身命名）上进行开发，每次commit时，著名这次提交的内容