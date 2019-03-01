<?php

namespace AppBundle\Command;

use AppBundle\Document\PullRequest;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GetReviewsByStateAndAuthorCommand extends AbstractStatisticsCommand
{
    protected function configure()
    {
        $this->setName('app:get-reviews-by-state-and-author');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $aggregationBuilder = $this->getDocumentManager()->createAggregationBuilder(PullRequest::class);

        $id = $aggregationBuilder->expr()
            ->field('user')
            ->expression('$reviews.user')
            ->field('state')
            ->expression('$reviews.state');

        $aggregationBuilder
            ->project()
                ->includeFields(['reviews'])
            ->unwind('$reviews')
            ->group()
                ->field('_id')
                ->concat('$reviews.user', '_', '$reviews.state')
                ->field('numReviews')
                ->sum(1)
        ;

        $results = $aggregationBuilder->execute()->toArray();

        file_put_contents('reviews-by-state-and-author.csv', $this->getResultsAsCsv($results));

        return 0;
    }

    private function getDocumentManager(): DocumentManager
    {
        return $this->getContainer()->get('doctrine_mongodb')->getManager();
    }
}
