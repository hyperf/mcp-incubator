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

namespace Hyperf\Mcp\Server\Transport;

use Hyperf\Mcp\Contract\TransportInterface;
use Hyperf\Mcp\Server\Protocol\Packer;
use Hyperf\Mcp\Types\Message\MessageInterface;
use Hyperf\Mcp\Types\Message\Notification;
use Hyperf\Mcp\Types\Message\Request;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Throwable;

class StdioTransport implements TransportInterface
{
    protected $onMessage;

    protected $onClose;

    protected $onError;

    private QuestionHelper $helper;

    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
        protected Packer $packer,
    ) {
        $this->helper = new QuestionHelper();
    }

    public function setOnMessage(callable $callback): void
    {
        $this->onMessage = $callback;
    }

    public function setOnClose(callable $callback): void
    {
        $this->onClose = $callback;
    }

    public function setOnError(callable $callback): void
    {
        $this->onError = $callback;
    }

    public function handleMessage(string $message): void
    {
        if ($this->onMessage) {
            call_user_func($this->onMessage, $message);
        }
    }

    public function handleClose(): void
    {
        if ($this->onClose) {
            call_user_func($this->onClose);
        }
    }

    public function handleError(Throwable $throwable): void
    {
        if ($this->onError) {
            call_user_func($this->onError, $throwable);
        }
    }

    public function sendMessage(MessageInterface $message): void
    {
        $this->send($this->packer->pack($message));
    }

    public function send(string $message): void
    {
        $this->output->writeln($message);
    }

    public function readMessage(): Notification|Request
    {
        $message = $this->helper->ask($this->input, $this->output, new Question(''));
        $message = $this->packer->unpack($message);
        if (! isset($message['id'])) {
            return new Notification(...$message);
        }
        return new Request(...$message);
    }
}
