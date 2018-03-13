<?php

namespace GitHubBundle\Controller;

use GitHubBundle\Services\GithubClient;
use JMS\Serializer\SerializerInterface;
use Twig_Environment;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;

class BaseController
{

    /**
     * @var \GitHubBundle\Services\GithubClient
     */
    protected $restClient;

    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Twig_Environment
     */
    protected $templateEngine;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * BaseController constructor.
     *
     * @param \GitHubBundle\Services\GithubClient $restClient
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param \Twig_Environment $templateEngine
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     */
    public function __construct(
      GithubClient $restClient,
      SerializerInterface $serializer,
      Twig_Environment $templateEngine,
      Router $router
    ) {
        $this->restClient = $restClient;
        $this->serializer = $serializer;
        $this->templateEngine = $templateEngine;
        $this->router = $router;
    }

    /**
     * This method is to be called whenever the authentication can not
     * be established
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToHomepage()
    {
        return new RedirectResponse(
          $this->router->generate('github_homepage')
        );
    }

}
