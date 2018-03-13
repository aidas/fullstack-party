<?php

namespace GitHubBundle\Services;

use Symfony\Component\HttpFoundation\Session\Session;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions as GRequestOptions;

/**
 * Class GithubClient
 *
 * Communication hub with GitHub API
 *
 * @package GitHubBundle\Services
 */
class GithubClient
{

    /**
     * Number of items to get per API call
     */
    const PER_PAGE = 50;

    /**
     * @var \GuzzleHttp\Client
     */
    private $restClient;

    /**
     * @var array
     */
    private $gitHubAppConf;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    private $session;

    /**
     * GithubClient constructor.
     *
     * @param \GuzzleHttp\Client $restClient
     * @param array $gitHubAppConf
     */
    public function __construct(Client $restClient, array $gitHubAppConf)
    {
        $this->restClient = $restClient;
        $this->gitHubAppConf = $gitHubAppConf;

        $this->session = new Session();
    }

    /**
     * Default API request settings with an optional debug-enabling flag
     *
     * @param bool $debug
     *
     * @return array
     */
    private function getDefaultRequestParams($debug = false)
    {
        return [
          'Accept' => 'application/vnd.github.v3+json',
          'query' => [
            'access_token' => $this->getAccessToken(),
            'per_page' => self::PER_PAGE,
          ],
          'debug' => $debug,
        ];
    }

    /**
     * Authenticates the user and retrieves their access token.
     * Existence of the access token in the session means the
     * user is already authenticated.
     *
     * @param string $code
     *
     * @return mixed|string
     */
    public function authenticate($code)
    {
        if ($this->getAccessToken() !== null) {
            return $this->getAccessToken();
        }

        $res = $this->restClient->post(
          'https://github.com/login/oauth/access_token',
          [
            GRequestOptions::JSON => [
              'client_id' => $this->gitHubAppConf['client_id'],
              'client_secret' => $this->gitHubAppConf['client_secret'],
              'code' => $code,
            ],
          ]
        );

        $res = json_decode($res->getBody(), true);
        if (isset($res['access_token'])) {
            $this->session->set('github_access_token', $res['access_token']);

            return $res['access_token'];
        } else {
            return false;
        }
    }

    /**
     * Gets a list of issues from a repo
     *
     * @param string $repo
     * @param string $state
     * @param int $page
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getRepoIssues($repo, $state, $page)
    {
        $endPoint = sprintf('repos/%s/issues', $repo);

        $defaultParams = $this->getDefaultRequestParams(false);
        $defaultParams['query']['state'] = $state;
        $defaultParams['query']['page'] = $page;

        $res = $this->restClient->request(
          'GET',
          $endPoint,
          $defaultParams
        );

        return $res;
    }

    /**
     * Gets a single issue from a repo
     *
     * @param string $repo
     * @param int $issueNumber
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getSingleIssue($repo, $issueNumber)
    {
        $endPoint = sprintf('repos/%s/issues/%d', $repo, $issueNumber);
        $res = $this->restClient->request(
          'GET',
          $endPoint,
          $this->getDefaultRequestParams(false)
        );

        return $res;
    }

    /**
     * Gets current GitHub API access token from
     * a session
     *
     * @return mixed|string
     */
    public function getAccessToken()
    {
        return $this->session->get('github_access_token');
    }

    /**
     * Gets current session
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSession()
    {
        return $this->session;
    }

}
