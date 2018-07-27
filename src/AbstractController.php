<?php
//
// +---------------------------------------------------------------------+
// | CODE INC. SOURCE CODE                                               |
// +---------------------------------------------------------------------+
// | Copyright (c) 2018 - Code Inc. SAS - All Rights Reserved.           |
// | Visit https://www.codeinc.fr for more information about licensing.  |
// +---------------------------------------------------------------------+
// | NOTICE:  All information contained herein is, and remains the       |
// | property of Code Inc. SAS. The intellectual and technical concepts  |
// | contained herein are proprietary to Code Inc. SAS are protected by  |
// | trade secret or copyright law. Dissemination of this information or |
// | reproduction of this material is strictly forbidden unless prior    |
// | written permission is obtained from Code Inc. SAS.                  |
// +---------------------------------------------------------------------+
//
// Author:   Joan Fabrégat <joan@codeinc.fr>
// Date:     03/07/2018
// Time:     13:07
// Project:  AbstractController
//
declare(strict_types=1);
namespace CodeInc\Psr15RouterMiddleware;
use Psr\Http\Message\ServerRequestInterface;


/**
 * Class AbstractController
 *
 * @package CodeInc\Psr15RouterMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
abstract class AbstractController implements ControllerInterface
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * AbstractController constructor.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return ServerRequestInterface
     */
    public function request():ServerRequestInterface
    {
        return $this->request;
    }
}