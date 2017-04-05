# CHANGELOG for 1.X

## v1.5.0

### 添加
- 命令行添加 `callSystem()` 接口, 允许至今调用系统命令, 如: ls, mkdir, sed, awk 等

### 变动
- Request 和 Response 使用 [Symfony/http-foundation](https://github.com/symfony/http-foundation) 代替

### 修正
- 修改 Request 获取 ip, 判断 pjax 问题
