<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Mcp\Server\Listener;

use Hyperf\Command\Command;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Mcp\Server\Annotation\Server;
use Hyperf\Mcp\Server\McpHandler;
use Hyperf\Mcp\Server\Transport\StdioTransport;
use Psr\Container\ContainerInterface;

class RegisterCommandListener implements ListenerInterface
{
    public function __construct(protected ConfigInterface $config, protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $classes = AnnotationCollector::getClassesByAnnotation(Server::class);

        foreach ($classes as $annotation) {
            if (! $annotation->signature) {
                continue;
            }

            $asCommand = new class($annotation) extends Command {
                protected bool $coroutine = false;

                public function __construct(Server $annotation)
                {
                    $this->signature = $annotation->signature;
                    $this->description = $annotation->description;
                    parent::__construct();
                }

                public function handle()
                {
                    $transport = new StdioTransport($this->input, $this->output);
                    $handler = new McpHandler('stdio', ApplicationContext::getContainer());
                    while (true) {
                        $request = $transport->readMessage();
                        if ($response = $handler->handle($request)) {
                            $transport->sendMessage($response);
                        }
                    }
                }
            };

            $hash = spl_object_hash($asCommand);
            $this->container->set($hash, $asCommand);
            $this->appendConfig('commands', $hash);
        }
    }

    private function appendConfig(string $key, $configValues): void
    {
        $configs = $this->config->get($key, []);
        $configs[] = $configValues;
        $this->config->set($key, $configs);
    }
}
