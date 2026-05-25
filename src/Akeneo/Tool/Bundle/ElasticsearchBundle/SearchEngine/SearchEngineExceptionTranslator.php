<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\SearchEngine;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use OpenSearch\Common\Exceptions\OpenSearchException;

/**
 * Translates OpenSearch PHP client exceptions into their Elasticsearch client
 * equivalents.
 *
 * This lets the rest of LibrePIM keep catching `ElasticsearchException`,
 * `ClientResponseException`, and `ServerResponseException` regardless of which
 * search engine is configured. The HTTP status is preserved on the attached
 * PSR-7 response so existing `$e->getResponse()->getStatusCode()` checks keep
 * working.
 */
final class SearchEngineExceptionTranslator
{
    public static function translate(\Throwable $e): \Throwable
    {
        if (!$e instanceof OpenSearchException) {
            return $e;
        }

        $status = (int) $e->getCode();
        $body = $e->getMessage();
        $isServerError = $status <= 0 || $status >= 500;
        $responseStatus = $status > 0 ? $status : 500;

        $exception = $isServerError
            ? new ServerResponseException($body, $status, $e)
            : new ClientResponseException($body, $status, $e);

        $exception->setResponse(new GuzzleResponse($responseStatus, [], $body));

        return $exception;
    }
}
