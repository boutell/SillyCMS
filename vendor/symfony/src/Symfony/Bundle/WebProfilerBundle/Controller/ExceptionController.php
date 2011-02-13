<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\WebProfilerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\ExceptionController as BaseExceptionController;

/**
 * ExceptionController.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ExceptionController extends BaseExceptionController
{
    /**
     * {@inheritdoc}
     */
    public function showAction(FlattenException $exception, DebugLoggerInterface $logger = null, $format = 'html')
    {
        $template = $this->container->get('kernel')->isDebug() ? 'exception' : 'error';
        $code = $this->getStatusCode($exception);

        return $this->container->get('templating')->renderResponse(
            'FrameworkBundle:Exception:'.$template.'.html.twig',
            array(
                'status_code'    => $code,
                'status_text'    => Response::$statusTexts[$code],
                'exception'      => $exception,
                'logger'         => null,
                'currentContent' => '',
            )
        );
    }
}
