# Reader 阅读器(服务端)
Web 版网络小说与漫画阅读器 

[客户端部署请点击](https://github.com/gavin66/reader-web)



# 截图

![](http://picture.shevchenko.ink/picgo/20190918182758.jpg)

![](http://picture.shevchenko.ink/picgo/20190918182815.jpg)

![](http://picture.shevchenko.ink/picgo/20190918182835.jpg)



# 使用

1. 下载项目

```sh
$ git clone https://github.com/gavin66/reader.git
```

2. 创建数据库

```sh
# 新增一个新的 MYSQL 数据库
# 执行 resources/table.sql 创建表
```

3. 安装

```sh
# 使用 composer 安装依赖
$ composer install

# 复制 .env.example 重命名为 .env 并修改(数据库,Redis,图片代理等)
$ cp .env.example .env && vim .env

# 生成应用密钥
$ php artisan  key:generate

# 新增一个用户,用于前端登录
$ php artisan user:generate --email your-email@gmail.com --password your-password
# 更新用户密码
$ php artisan user:generate --update --email your-email@gmail.com --password your-new-password
```

4. 将 `public/index.php` 部署到你的服务器



# 注意

* `.env` 中图片代理地址一定要填写,最差也要填写本服务的地址.漫画图片加载速度取决于你的服务器带宽.
* 图片代理是为了防盗链,没有它漫画图片无法访问.
* 有一些 bug, 但不影响使用, 今后会慢慢解决



## 免责声明

本项目仅作为爬虫技术交流学习，**切勿非法使用**

**请大家支持正版**



# 最后

如果你喜欢这个项目, 麻烦给我一个 star ⭐. 时间充裕时会持续维护这个项目.

如果有任何建议与意见欢迎提 issues.