<?php

namespace ShareMonkey\ShareMonkeyBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use React\EventLoop\LoopInterface;
use ShareMonkey\Model\Slack\UserId;
use ShareMonkey\Document\User;
use ShareMonkey\Service\Slack\ApiClient;
use Slack\User as SlackUser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportSlackUsersCommand extends Command
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct(
        ApiClient $client,
        LoopInterface $loop,
        ObjectManager $objectManager
    ) {
        parent::__construct();
        $this->client = $client;
        $this->loop = $loop;
        $this->objectManager = $objectManager;
    }

    protected function configure()
    {
        $this
            ->setName('sharemonkey:import-slack-users')
            ->setDescription('Import info of the slack users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client->getUsers()->then(
            function ($users) use ($output) {
                $this->importUsers($users, $output);
            }
        );
        $this->loop->run();
    }

    /**
     * @param SlackUser[] $users
     */
    private function importUsers(array $users, OutputInterface $output)
    {
        foreach ($users as $slackUser) {
            if ($slackUser->isDeleted() || $slackUser->data['is_bot'] === true) {
                $output->writeln(sprintf('Skipping %s (%s)', $slackUser->data['name'], $slackUser->getId()));
                continue;
            }

            $output->writeln(sprintf('Importing %s', $slackUser->getEmail()));

            $user = User::fromSlack(
                new UserId($slackUser->data['id']),
                $slackUser->data['name'],
                $slackUser->getEmail(),
                $slackUser->data['profile']['image_72']
            );
            $this->objectManager->persist($user);
        }

        $this->objectManager->flush();
    }
}
