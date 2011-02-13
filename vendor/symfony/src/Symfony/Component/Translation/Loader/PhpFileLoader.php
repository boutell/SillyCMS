<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation\Loader;

use Symfony\Component\Translation\Resource\FileResource;

/**
 * PhpFileLoader loads translations from PHP files returning an array of translations.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class PhpFileLoader extends ArrayLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $messages = require($resource);

        $catalogue = parent::load($messages, $locale, $domain);
        $catalogue->addResource(new FileResource($resource));

        return $catalogue;
    }
}
