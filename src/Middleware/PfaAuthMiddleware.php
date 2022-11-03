<?php

namespace Opf\Middleware;

use Opf\Util\Authentication\PfaBasicAuthenticator;
use Opf\Util\Authentication\PfaBearerAuthenticator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

class PfaAuthMiddleware implements MiddlewareInterface
{
    /** @var \Opf\Util\Authentication\PfaBasicAuthenticator */
    private $basicAuthenticator;

    /** @var \Opf\Util\Authentication\PfaBearerAuthenticator */
    private $bearerAuthenticator;

    public function __construct(ContainerInterface $container)
    {
        $this->basicAuthenticator = $container->get('BasicAuthenticator');
        $this->bearerAuthenticator = $container->get('BearerAuthenticator');
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // If no 'Authorization' header supplied, we directly return a 401
        if (!$request->hasHeader('Authorization')) {
            return new Response(401);
        }

        // $request->getHeader() gives back a string array, hence the need for [0]
        $authHeader = $request->getHeader('Authorization')[0];

        // Obtain the auth type and the supplied credentials
        $authHeaderSplit = explode(' ', $authHeader);
        $authType = $authHeaderSplit[0];
        $authCredentials = $authHeaderSplit[1];

        // This is a flag that tracks whether auth succeeded or not
        $isAuthSuccessful = false;

        // TODO: Since authorization is currently WIP, this part below is commented out,
        // as it causes some bugs
        /*
        // Obtain request information that is passed for the authorization checks
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $resourceType = explode('.', $route->getName())[0];
        $scimOperation = explode('.', $route->getName())[1];

        // Resource ID in this case is of the format mailbox@domain.tld
        // It's important to identify both a single mailbox, as well as the domain it belongs to
        if (strcmp($request->getMethod(), 'POST') === 0) {
            // In case of POST requests, we use the 'userName' field in the request body as resource ID
            $resourceId = json_decode($request->getBody(), true)['userName'];
        } else {
            // Otherwise, we get it from the 'id' path parameter
            // Note: this can be null in case of requests, such as GET /Users
            // Hence, we check further down the auth chain to know what type of request we''re dealing with
            $resourceId = $route->getArgument("id");
        }
        $authorizationInfo = array(
            'resourceType' => $resourceType,
            'scimOperation' => $scimOperation,
            'resourceId' => $resourceId
        );*/
        $authorizationInfo = [];

        // Call the right authenticator, based on the auth type
        if (strcmp($authType, 'Basic') === 0) {
            $isAuthSuccessful = $this->basicAuthenticator->authenticate($authCredentials, $authorizationInfo);
        } elseif (strcmp($authType, 'Bearer') === 0) {
            $isAuthSuccessful = $this->bearerAuthenticator->authenticate($authCredentials, $authorizationInfo);
        }

        // If everything went fine, let the request pass through
        if ($isAuthSuccessful) {
            return $handler->handle($request);
        }

        // If something didn't go right so far, then return a 401
        return new Response(401);
    }
}
