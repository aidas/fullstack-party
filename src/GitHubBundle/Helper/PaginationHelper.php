<?php

namespace GitHubBundle\Helper;

use GuzzleHttp\Psr7\Response;

/***
 * Class PaginationHelper
 *
 * Simple selection of methods to work with
 * pagination data
 *
 * @package GitHubBundle\Helper
 */
class PaginationHelper
{

    /**
     * Extracts first/next/prev/last type of links from Guzzle response
     *
     * @param \GuzzleHttp\Psr7\Response $res
     *
     * @return array|bool
     */
    public static function getPaginationData(Response $res)
    {
        $pageData = $res->getHeader('Link');
        if (is_array($pageData) && !empty($pageData[0])) {
            $pageData = explode(',', $pageData[0]);

            $data = [];
            foreach ($pageData as $pagination) {
                if (preg_match(
                  '/^.*rel=\"(prev|next|last|first)\"$/',
                  $pagination,
                  $matches
                )) {
                    $search = '; rel="'.$matches[1].'"';
                    $data[$matches[1]] = trim(
                      str_replace([$search, '<', '>'], '', $pagination)
                    );
                }
            }

            return $data;
        } else {
            return false;
        }
    }

    /**
     * Extracts a page numeric value of first/next/prev/last link
     *
     * @param mixed $res
     * @param string $identifier
     *
     * @return bool
     */
    public static function getPageNumber($res, $identifier)
    {
        if ($res instanceof Response) {
            $res = self::getPaginationData($res);
        }

        if (isset($res[$identifier])) {
            if (preg_match('/.*page=(\d+)$/', $res[$identifier], $matches)) {
                return $matches[1];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
