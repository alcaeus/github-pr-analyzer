<?php

namespace AppBundle\Command;

use AppBundle\Document\PullRequest;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class WeeklyPullRequestStatsCommand extends AbstractStatisticsCommand
{
    protected function configure()
    {
        $this->setName('app:weekly-pr-statistics');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $aggregationBuilder = $this->getDocumentManager()->createAggregationBuilder(PullRequest::class);

        $closedAt = $aggregationBuilder->expr()->ifNull('$mergedAt', '$closedAt');
        $timeOpen = $aggregationBuilder->expr()->subtract($closedAt, '$createdAt');

        $aggregationBuilder
            ->group()
                ->field('_id')
                ->week('$createdAt')
                ->field('numPullRequests')
                ->sum(1)
                ->field('linesAdded')
                ->avg('$linesAdded')
                ->field('linesRemoved')
                ->avg('$linesRemoved')
                ->field('changedFiles')
                ->avg('$changedFiles')
                ->field('numCommits')
                ->avg('$numCommits')
                ->field('numComments')
                ->avg('$numComments')
                ->field('numReviewComments')
                ->avg('$numReviewComments')
                ->field('avgTimeOpen')
                ->avg($timeOpen)
                ->field('maxTimeOpen')
                ->max($timeOpen)
            ->sort(['_id' => 1]);
        ;

        $weeklyPullRequests = $aggregationBuilder->execute()->toArray();

        file_put_contents('weekly-pr-statistics.csv', $this->getResultsAsCsv($weeklyPullRequests));

        return 0;
    }

    private function getDocumentManager(): DocumentManager
    {
        return $this->getContainer()->get('doctrine_mongodb')->getManager();
    }
}
