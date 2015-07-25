<?php

namespace ShareMonkey\ShareMonkeyBundle\Command;

use React\EventLoop\LoopInterface;
use ShareMonkey\Model\Slack\Message;
use ShareMonkey\Model\Slack\Reaction;
use ShareMonkey\Service\Slack\IncomingLinkProcessor;
use ShareMonkey\Service\Slack\ReactionProcessor;
use ShareMonkey\ShareMonkeyBundle\Service\Slack\RealTimeClient;
use Slack\Payload;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListenCommand extends Command
{
    /**
     * @var IncomingLinkProcessor
     */
    private $incomingLinkProcessor;

    /**
     * @var RealTimeClient
     */
    private $client;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var ReactionProcessor
     */
    private $reactionProcessor;

    public function __construct(
        RealTimeClient $client,
        LoopInterface $loop,
        IncomingLinkProcessor $incomingLinkProcessor,
        ReactionProcessor $reactionProcessor
    ) {
        parent::__construct();
        $this->client = $client;
        $this->loop = $loop;
        $this->incomingLinkProcessor = $incomingLinkProcessor;
        $this->reactionProcessor = $reactionProcessor;
    }

    protected function configure()
    {
        $this
            ->setName('sharemonkey:listen')
            ->setDescription('Listen to Slack channels and catch links');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->client;

        $client->on('message', function (Payload $payload) use ($output) {
            $this->onMessage($payload, $output);
        });

        $client->on('reaction_added', function (Payload $payload) use ($client, $output) {
            $this->onReaction($payload, $output);
        });

        $client->connect()->then(function () use ($client, $output) {
            $output->writeln('<info>Connected</info>');
        });

        $this->loop->run();
    }

    /**
     * @param Payload $payload
     * @param OutputInterface $output
     */
    private function onMessage(Payload $payload, OutputInterface $output)
    {
        try {
            // Always ignore messages without a user
            if ($payload->offsetExists('user') == false) {
                return;
            }

            $message = Message::fromSlack(
                $payload->offsetGet('text'),
                $payload->offsetGet('ts'),
                $payload->offsetGet('user')
            );
            $this->incomingLinkProcessor->process($message);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error: %s, %s</error>', get_class($e), $e->getMessage()));
        }
    }

    /**
     * @param Payload $payload
     * @param OutputInterface $output
     */
    private function onReaction(Payload $payload, OutputInterface $output)
    {
        try {
            // Always ignore messages without a user
            if ($payload->offsetExists('user') == false) {
                return;
            }

            $reaction = Reaction::fromSlack(
                $payload->offsetGet('reaction'),
                $payload->offsetGet('event_ts'),
                $payload->offsetGet('item')['ts'],
                $payload->offsetGet('user')
            );
            $this->reactionProcessor->process($reaction);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error: %s, %s</error>', get_class($e), $e->getMessage()));
        }
    }
}
