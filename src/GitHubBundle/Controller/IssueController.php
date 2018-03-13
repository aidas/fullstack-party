<?php

namespace GitHubBundle\Controller;

use GitHubBundle\Services\GithubClient;
use GitHubBundle\Helper\RestHelper;
use GitHubBundle\Helper\PaginationHelper;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IssueController
 *
 * Controller responsible for GitHub issue querying and display
 *
 * @package GitHubBundle\Controller
 */
class IssueController extends BaseController
{

    /**
     * Base deserialization method
     *
     * @param object|string $body
     * @param string $type
     *
     * @return array|\JMS\Serializer\scalar|object
     *
     * @todo - move to another class, like serialization helper
     */
    private function deserializeBase($body, $type)
    {
        if ($body instanceof GuzzleResponse) {
            $body = $body->getBody();
        }
        return $this->serializer->deserialize($body, $type, 'json');
    }

    /**
     * Issue collection deserializer
     *
     * @param object|string $body
     *
     * @return array
     *
     * @todo - move to another class, like serialization helper
     */
    private function deserializeIssueList($body)
    {
        return $this->deserializeBase($body, 'array<GitHubBundle\Entity\Issue>');
    }

    /**
     * Single issue deserializer
     *
     * @param object|string $body
     *
     * @return \GitHubBundle\Entity\Issue
     *
     * @todo - move to another class, like serialization helper
     */
    private function deserializeIssue($body)
    {
        return $this->deserializeBase($body, 'GitHubBundle\Entity\Issue');
    }

    /**
     * Generates an error response
     *
     * @param $result
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function errorResponse($result)
    {
        $message = 'There has been a problem with your request <br />Payload:<br />'.$result->getBody();

        return new Response($message, $result->getStatusCode());
    }

    /**
     * Gets a number of issues on the last paginated page of a repo query result
     *
     * @param string $repo
     * @param string $state
     * @param int $lastPageNo
     *
     * @return int
     */
    private function getNumberOfIssuesOnLastPage($repo, $state, $lastPageNo)
    {
        $lastPageResult = $this->restClient->getRepoIssues(
          $repo,
          $state,
          $lastPageNo
        );
        $itemsOnLastPage = count(
          json_decode($lastPageResult->getBody(), true)
        );
        $itemsOnPrevPages = ($lastPageNo - 1) * GithubClient::PER_PAGE;

        return $itemsOnLastPage + $itemsOnPrevPages;
    }

    /**
     * Gets total number of issues per repo on a give state
     *
     * @param string $repo
     * @param string $state
     *
     * @return int
     */
    private function getTotalNumberOfIssues($repo, $state)
    {
        //get issues of the 1st page
        $issues = $this->restClient->getRepoIssues($repo, $state, 1);
        if (!RestHelper::isSuccessful($issues->getStatusCode())) {
            return $this->errorResponse($issues);
        }

        if ($lastPageNo = PaginationHelper::getPageNumber($issues, 'last')) {
            //if 'last' is present in the returned pagination data
            return $this->getNumberOfIssuesOnLastPage($repo, $state, $lastPageNo);
        } else {
            //we're already on the last page, which is a page #1!
            $issuesAssoc = json_decode($issues->getBody(), true);
            return (empty($issuesAssoc)) ? 0 : count($issuesAssoc);
        }
    }

    /**
     * Gets a list of issues from a given repo
     *
     * @param string $repo
     * @param string $state
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function issueList($repo, $state, $page = 1)
    {
        if ($this->restClient->getAccessToken() == null) {
            return $this->redirectToHomepage();
        }

        $res = $this->restClient->getRepoIssues($repo, $state, $page);

        if (!RestHelper::isSuccessful($res->getStatusCode())) {
            return $this->errorResponse($res);
        }

        $issues = $this->deserializeIssueList($res->getBody());
        $paginationData = PaginationHelper::getPaginationData($res);
        $totals = [];

        //if the queried state is 'open' or 'closed', get it's total issue number
        if (in_array($state, ['open', 'closed'])) {
            if ($lastPageNo = PaginationHelper::getPageNumber(
              $paginationData,
              'last'
            )
            ) {
                //if 'last' is present in the returned pagination data
                $total = $this->getNumberOfIssuesOnLastPage($repo, $state, $lastPageNo);
            } else {
                //we're already on the last page
                $itemsOnCurrentPage = count($issues);
                $itemsOnPrevPages = ($page - 1) * GithubClient::PER_PAGE;
                $total = $itemsOnCurrentPage + $itemsOnPrevPages;
            }

            $totals[$state] = $total;
        }

        switch ($state) {
            case 'closed':
                $totals['open'] = $this->getTotalNumberOfIssues($repo, 'open');
                break;
            case 'open':
                $totals['closed'] = $this->getTotalNumberOfIssues($repo, 'closed');
                break;
            case 'all':
                $totals['open'] = $this->getTotalNumberOfIssues($repo, 'open');
                $totals['closed'] = $this->getTotalNumberOfIssues($repo,'closed');
        }

        $content = $this->templateEngine->render(
          '@GitHub/Default/issueList.html.twig',
          [
            'repo' => $repo,
            'issues' => $issues,
            'paginationData' => $paginationData,
            'totals' => $totals,
          ]
        );

        return new Response($content);
    }

    /**
     * Queries a single issue identified by it's number and a repo
     *
     * @param string $repo
     * @param int $issueNumber
     *x
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function issueAction($repo, $issueNumber)
    {
        if ($this->restClient->getAccessToken() == null) {
            return $this->redirectToHomepage();
        }

        $res = $this->restClient->getSingleIssue($repo, $issueNumber);

        if (!RestHelper::isSuccessful($res->getStatusCode())) {
            return $this->errorResponse($res);
        }

        $content = $this->templateEngine->render(
          '@GitHub/Default/issue.html.twig',
          [
            'repo' => $repo,
            'issue' => $this->deserializeIssue($res),
          ]
        );

        return new Response($content);
    }
}
