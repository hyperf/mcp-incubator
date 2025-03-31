# Model Context Protocol (MCP)

开始使用模型上下文协议 Model Context Protocol (MCP)。

## 安装

```bash
composer require hyperf/mcp-incubator
```

## 使用

### Stdio


```php
<?php

use Hyperf\Mcp\Annotation\Tool;
use Hyperf\Mcp\Server\Annotation\Server;

#[Server(name: 'stdio', signature: 'mcp:command', description: '这是一个测试命令')]
class Foo
{
    #[Tool(name: 'sum', description: '计算两个数的和', serverName: 'stdio')]
    public function sum(#[Description('这是A参数')] int $a, #[Description('这是B参数')] int $b = 0): int
    {
        return $a + $b;
    }
}
```



### SSE

修改`autoload/server.php` (新增 `mcp-sse` 协议)

注意: SSE 协议属于**有状态长连接**协议, 请确保你的 Nginx 配置支持长连接, 并且根据`sessionId` 参数进行负载均衡。

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;
use Hyperf\Mcp\Server\McpServer;

return [
    'type' => Hyperf\Server\CoroutineServer::class, # 建议协程风格
    'servers' => [
        'mcp-sse' => [
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 3000,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [McpServer::class, 'onRequest'],
                Event::ON_CLOSE => [McpServer::class, 'onClose'],
            ],
            'options' => [
                'mcp_path' => '/sse',
            ],
        ],
    ],
];
```

```php
<?php

use Hyperf\Mcp\Annotation\Tool;
use Hyperf\Mcp\Server\Annotation\Server;

#[Server(name: 'mcp-sse', description: '这是一个测试命令')]
class Foo
{
    #[Tool(name: 'sum', description: '计算两个数的和', serverName: 'mcp-sse')]
    public function sum(#[Description('这是A参数')] int $a, #[Description('这是B参数')] int $b = 0): int
    {
        return $a + $b;
    }
}
```
