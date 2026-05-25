<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\SearchEngine;

use OpenSearch\ClientBuilder as OpenSearchClientBuilder;

/**
 * Returns the search-engine client builder selected by the `SEARCH_ENGINE`
 * environment variable.
 *
 * - `opensearch` (default) -> native OpenSearch client builder
 * - `elasticsearch`        -> Elasticsearch client wrapped in an adapter that
 *                             exposes the same API as the OpenSearch client
 *
 * Both returned builders expose `setHosts(array $hosts)` and `build()`, so
 * LibrePIM's services can stay engine-agnostic.
 */
final class SearchEngineClientBuilderFactory
{
    public const ENGINE_OPENSEARCH = 'opensearch';
    public const ENGINE_ELASTICSEARCH = 'elasticsearch';

    public static function createBuilder(string $searchEngine = self::ENGINE_OPENSEARCH): object
    {
        return match (strtolower(trim($searchEngine))) {
            self::ENGINE_ELASTICSEARCH, 'es' => new ElasticsearchClientBuilderAdapter(),
            default => new OpenSearchClientBuilder(),
        };
    }
}
