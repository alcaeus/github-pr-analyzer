<?php

namespace AppBundle\Command;

use AppBundle\Document\PullRequest;
use Doctrine\ODM\MongoDB\DocumentManager;
use Github\Api\PullRequest as PullRequestApi;
use Github\Client;
use Github\Exception\ApiLimitExceedException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ReadPullRequestsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:read-pull-requests')
            ->setDescription(<<<DESC
Reads one or more pull requests from the GitHub API and stores them in the database.

To read a single pull request, call this command with a pull request number as an argument.
To read more pull requests, use the --from and --to options.
DESC
)
            ->addArgument('username', InputArgument::REQUIRED, 'The username the GitHub repository belongs to')
            ->addArgument('repository', InputArgument::REQUIRED, 'The name of the repository')
            ->addArgument('number', InputArgument::OPTIONAL, 'A single pull request number to fetch')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'The lower bound of a range of pull requests to fetch')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'The upper bound of a range of pull requests to fetch');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $repository = $input->getArgument('repository');

        if ($input->getArgument('number')) {
            $numbers = [(int)$input->getArgument('number')];
        } elseif ($input->getOption('from') && $input->getOption('to')) {
            $numbers = range((int) $input->getOption('from'), (int) $input->getOption('to'));
        } else {
            $output->writeln('Invalid input given - provide a single pull request or range');
            return 1;
        }

        $reader = new \AppBundle\Reader\PullRequest($this->getGithubClient());

        $progress = new ProgressBar($output, count($numbers));
        $progress->display();
        try {
            foreach ($numbers as $number) {
                $this->getDocumentManager()->persist($reader->read($username, $repository, $number));
                $progress->advance();

                if ($number % 10 === 0) {
                    $this->getDocumentManager()->flush();
                }
            }
            $progress->finish();
            $this->getDocumentManager()->flush();

            $output->writeln(['', 'All pull requests stored']);
        } catch (ApiLimitExceedException $e) {
            if ($input->getOption('to')) {
                $commandOptions = "--from={$number} --to={$input->getOption('to')}";
            } else {
                $commandOptions = (string) $number;
            }

            $command = "{$this->getName()} {$username} {$repository} {$commandOptions}";

            $message = <<<MESSAGE
<error>API rate limit reached (%d requests per hour).</error>

App fetched pull requests have been stored. The limit will be reset at %s. To continue where you left off, run the following command:
%s
MESSAGE;

            $output->writeln(['', sprintf($message, $e->getLimit(), date('r', $e->getResetTime()), $command)]);
            return 1;
        }

        return 0;
    }

    private function getDocumentManager(): DocumentManager
    {
        return $this->getContainer()->get('doctrine_mongodb')->getManager();
    }

    private function getGithubClient(): Client
    {
        return $this->getContainer()->get('github_api_client');
    }
}
