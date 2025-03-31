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

use Hyperf\Mcp\Server\Protocol\Packer;
use Hyperf\Mcp\Transport\TransportInterface;
use Hyperf\Mcp\Types\Message\MessageInterface;
use Hyperf\Mcp\Types\Message\Notification;
use Hyperf\Mcp\Types\Message\Request;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class StdioTransport implements TransportInterface
{
    private QuestionHelper $helper;

    public function __construct(
        protected InputInterface $input,
        protected OutputInterface $output,
        protected Packer $packer,
    ) {
        $this->helper = new QuestionHelper();
    }

    public function sendMessage(MessageInterface $message): void
    {
        $this->output->writeln($this->packer->pack($message));
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
