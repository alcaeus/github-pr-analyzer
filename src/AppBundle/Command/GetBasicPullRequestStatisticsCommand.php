<?php

namespace AppBundle\Command;

use AppBundle\Document\PullRequest;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GetBasicPullRequestStatisticsCommand extends AbstractStatisticsCommand
{
    protected function configure()
    {
        $this->setName('app:basic-pr-statistics');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $aggregationBuilder = $this->getDocumentManager()->createAggregationBuilder(PullRequest::class);
        $aggregationBuilder
            ->group()
                ->field('_id')
                ->expression(null)
                ->field('linesAdded')
                ->sum('$linesAdded')
                ->field('linesRemoved')
                ->sum('$linesRemoved')
                ->field('numCommits')
                ->sum('$numCommits')
                ->field('numComments')
                ->sum('$numComments')
                ->field('numReviewComments')
                ->sum('$numReviewComments')
        ;

        $results = $aggregationBuilder->execute()->toArray();

        file_put_contents('basic-pr-statistics.csv', $this->getResultsAsCsv($results));

        return 0;
    }

    private function getDocumentManager(): DocumentManager
    {
        return $this->getContainer()->get('doctrine_mongodb')->getManager();
    }
}
