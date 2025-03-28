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

namespace Hyperf\Mcp\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\JsonRpc\JsonRpcNormalizer;
use Hyperf\JsonRpc\JsonRpcTransporter;
use Hyperf\JsonRpc\PathGenerator;
use Hyperf\Mcp\Protocol\DataFormatter;
use Hyperf\Mcp\Protocol\Packer;
use Hyperf\Rpc\ProtocolManager;

class RegisterProtocolListener implements ListenerInterface
{
    public function __construct(private ProtocolManager $protocolManager)
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
        $this->protocolManager->register('mcp-sse', [
            'packer' => Packer::class,
            'transporter' => JsonRpcTransporter::class,
            'path-generator' => PathGenerator::class,
            'data-formatter' => DataFormatter::class,
            'normalizer' => JsonRpcNormalizer::class,
        ]);
    }
}
