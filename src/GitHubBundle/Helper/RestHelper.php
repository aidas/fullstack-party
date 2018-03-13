<?php

namespace GitHubBundle\Helper;

class RestHelper {

    /**
     * @param int $statusCode
     *
     * @return bool
     */
    public static function isSuccessful($statusCode)
    {
        return ($statusCode >= 200 && $statusCode < 300) || $statusCode == 304;
    }

}
