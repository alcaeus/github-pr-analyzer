<?php declare(strict_types = 1);

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractStatisticsCommand extends ContainerAwareCommand
{
    protected function combineResults(array ...$rawResults): array
    {
        $results = [];

        foreach ($rawResults as $rawResult) {
            foreach ($rawResult as $row) {
                $id = $row['_id'];
                $results[$id] = array_merge($results[$id] ?? [], $row);
            }
        }

        return $results;
    }

    protected function getResultsAsCsv(array $results): string
    {
        $stream = fopen('php://memory', 'w+');
        fputcsv($stream, array_keys(array_values($results)[0]));
        array_map(function (array $row) use (&$stream) {
            fputcsv($stream, $row);
        }, $results);

        $csv = stream_get_contents($stream, -1, 0);

        fclose($stream);

        return $csv;
    }
}
