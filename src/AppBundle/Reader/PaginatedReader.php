<?php declare(strict_types = 1);

namespace AppBundle\Reader;

abstract class PaginatedReader
{
    protected function paginate(\Closure $fetch, int $perPage = 30): array
    {
        $results = [];
        $page = 1;

        do {
            $returned = $fetch($page, $perPage);
            $results = array_merge($results, $returned);

            $page++;
        } while (count($returned) == $perPage);

        return $results;
    }
}
