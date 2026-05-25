<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\SearchEngine;

use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Elasticsearch\Response\Elasticsearch as ElasticResponse;
use OpenSearch\Common\Exceptions\BadRequest400Exception;
use OpenSearch\Common\Exceptions\Conflict409Exception;
use OpenSearch\Common\Exceptions\Missing404Exception;
use OpenSearch\Common\Exceptions\OpenSearchException;
use OpenSearch\Common\Exceptions\ServerErrorResponseException;
use Psr\Http\Message\ResponseInterface;

/**
 * Translates Elasticsearch PHP client exceptions into their OpenSearch client
 * equivalents.
 *
 * This lets the rest of LibrePIM catch only OpenSearch exception types
 * (`OpenSearchException`, `BadRequest400Exception`, `Conflict409Exception`, ...)
 * regardless of whether the configured search engine is OpenSearch or
 * Elasticsearch. The HTTP status is carried as the exception code, and the
 * response body as the message — matching the OpenSearch client's behaviour.
 */
final class SearchEngineExceptionTranslator
{
    public static function translate(\Throwable $e): \Throwable
    {
        // Already an OpenSearch exception, or not search-engine related: leave as-is.
        if ($e instanceof OpenSearchException || !$e instanceof ElasticsearchException) {
            return $e;
        }

        $status = null;
        $body = $e->getMessage();

        if (method_exists($e, 'getResponse')) {
            $response = $e->getResponse();
            if ($response instanceof ElasticResponse) {
                // Elasticsearch PHP 8.x: not PSR-7 but exposes getStatusCode()/getBody().
                $status = $response->getStatusCode();
                $body = (string) $response->getBody();
            } elseif ($response instanceof ResponseInterface) {
                $status = $response->getStatusCode();
                $body = (string) $response->getBody();
            } elseif (is_object($response) && method_exists($response, 'getStatusCode')) {
                // Defensive fallback for any other response object that exposes the same shape.
                $status = $response->getStatusCode();
                if (method_exists($response, 'getBody')) {
                    $body = (string) $response->getBody();
                }
            }
        }

        // Fall back to the exception's own code so downstream consumers
        // (bulk retry/backoff, controller error mapping) can still inspect it.
        if (null === $status && 0 !== $e->getCode()) {
            $status = $e->getCode();
        }

        return match (true) {
            404 === $status => new Missing404Exception($body, 404, $e),
            409 === $status => new Conflict409Exception($body, 409, $e),
            null !== $status && $status >= 500 => new ServerErrorResponseException($body, $status, $e),
            default => new BadRequest400Exception($body, $status ?? 0, $e),
        };
    }
}
