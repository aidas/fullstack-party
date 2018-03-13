<?php

namespace GitHubBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class HomepageController
 *
 * @package GitHubBundle\Controller
 */
class HomepageController extends BaseController
{

    /**
     * First page (aka homepage) logic
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $content = $this->templateEngine->render(
          '@GitHub/Default/index.html.twig',
          [
            'is_logged_in' => $this->restClient->getAccessToken(),
            'repos' => [
              'tesonet/fullstack-party',
              'symfony/symfony',
            ],
          ]
        );

        return new Response($content);
    }
}
