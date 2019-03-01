<?php

namespace AppBundle\Command;

use AppBundle\Document\PullRequest;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GetBasicAuthorStatisticsCommand extends AbstractStatisticsCommand
{
    protected function configure()
    {
        $this->setName('app:basic-author-statistics');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $aggregationBuilder = $this->getDocumentManager()->createAggregationBuilder(PullRequest::class);
        $aggregationBuilder
            ->group()
                ->field('_id')
                ->expression('$user')
                ->field('numPullRequests')
                ->sum(1);

        $numPullRequests = $aggregationBuilder->execute()->toArray();

        $aggregationBuilder = $this->getDocumentManager()->createAggregationBuilder(PullRequest::class);
        $aggregationBuilder
            ->unwind('$comments')
            ->group()
                ->field('_id')
                ->expression('$comments.user')
                ->field('numComments')
                ->sum(1);

        $numComments = $aggregationBuilder->execute()->toArray();

        $aggregationBuilder = $this->getDocumentManager()->createAggregationBuilder(PullRequest::class);
        $aggregationBuilder
            ->unwind('$reviews')
            ->group()
                ->field('_id')
                ->expression('$reviews.user')
                ->field('numReviews')
                ->sum(1);

        $numReviews = $aggregationBuilder->execute()->toArray();

        $aggregationBuilder = $this->getDocumentManager()->createAggregationBuilder(PullRequest::class);
        $aggregationBuilder
            ->project()
                ->includeFields(['reviews'])
            ->unwind('$reviews')
            ->unwind('$reviews.comments')
            ->group()
                ->field('_id')
                ->expression('$reviews.comments.user')
                ->field('numReviewComments')
                ->sum(1);

        $numReviewComments = $aggregationBuilder->execute()->toArray();

        $results = $this->combineResults($numPullRequests, $numComments, $numReviews, $numReviewComments);
        var_dump($results);
        file_put_contents('basic-author-statistics.csv', $this->getResultsAsCsv($results));

        return 0;
    }

    private function getDocumentManager(): DocumentManager
    {
        return $this->getContainer()->get('doctrine_mongodb')->getManager();
    }
}
