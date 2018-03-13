<?php

namespace GitHubBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthController
 *
 * Very simple selection of methods to deal
 * with the authentication operations
 *
 * @package GitHubBundle\Controller
 */
class AuthController extends BaseController
{

    /**
     * A callback URL that user is redirected to after logging in with GitHub
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function inboundAction(Request $request)
    {
        if (!$this->restClient->authenticate($request->get('code'))) {
            return new Response('Authentication unsuccessful', '403');
        } else {
            return $this->redirectToHomepage();
        }
    }

    /**
     * Logout action
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction()
    {
        $this->restClient->getSession()->remove('github_access_token');

        return $this->redirectToHomepage();
    }

}
