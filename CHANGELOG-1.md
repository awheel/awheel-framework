# CHANGELOG for 1.X

## v1.5.6
- 405 异常信息附加 pathInfo

## v1.5.5
- 修正 Request::url() 获取当前 url
- Router 添加获取当前动作
- 增加 Request 获取路由参数

## v1.5.4
- Request::file() 注释调整, 返回值注释改为 UploadFile
- 修正 put/delete/patch/options 识别

## v1.5.3
- 修正配置获取错误

## v1.5.2
- 修正获取请求头部信息
- Request 添加 acceptWebP 方法判断浏览器是否支持 webp
- 时区从配置文件读取

## v1.5.1

- 命令行添加 `callSystem()` 接口, 允许至今调用系统命令, 如: ls, mkdir, sed, awk 等
- Request 和 Response 使用 [Symfony/http-foundation](https://github.com/symfony/http-foundation) 代替
- 修改 Request 获取 ip, 判断 pjax 问题
