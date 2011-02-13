<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Translation;

use Symfony\Component\Translation\Loader\LoaderInterface;

/**
 * Translator.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Translator implements TranslatorInterface
{
    protected $catalogues;
    protected $locale;
    protected $fallbackLocale;
    protected $loaders;
    protected $resources;
    protected $selector;

    /**
     * Constructor.
     *
     * @param string          $locale   The locale
     * @param MessageSelector $selector The message selector for pluralization
     */
    public function __construct($locale = null, MessageSelector $selector)
    {
        $this->locale = $locale;
        $this->selector = $selector;
        $this->loaders = array();
        $this->resources = array();
        $this->catalogues = array();
    }

    /**
     * Adds a Loader.
     *
     * @param string          $format The name of the loader (@see addResource())
     * @param LoaderInterface $loader A LoaderInterface instance
     */
    public function addLoader($format, LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }

    /**
     * Adds a Resource.
     *
     * @param string $format   The name of the loader (@see addLoader())
     * @param mixed  $resource The resource name
     * @param string $locale   The locale
     * @param string $domain   The domain
     */
    public function addResource($format, $resource, $locale, $domain = 'messages')
    {
        if (!isset($this->resources[$locale])) {
            $this->resources[$locale] = array();
        }

        $this->resources[$locale][] = array($format, $resource, $domain);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the fallback locale.
     *
     * @param string $locale The fallback locale
     */
    public function setFallbackLocale($locale)
    {
        // needed as the fallback locale is used to fill-in non-yet translated messages
        $this->catalogues = array();

        $this->fallbackLocale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        if (!isset($locale)) {
            $locale = $this->getLocale();
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        return strtr($this->catalogues[$locale]->get($id, $domain), $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = 'messages', $locale = null)
    {
        if (!isset($locale)) {
            $locale = $this->getLocale();
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        return strtr($this->selector->choose($this->catalogues[$locale]->get($id, $domain), (int) $number, $locale), $parameters);
    }

    protected function loadCatalogue($locale)
    {
        $this->catalogues[$locale] = new MessageCatalogue($locale);
        if (!isset($this->resources[$locale])) {
            return;
        }

        foreach ($this->resources[$locale] as $resource) {
            if (!isset($this->loaders[$resource[0]])) {
                throw new \RuntimeException(sprintf('The "%s" translation loader is not registered.', $resource[0]));
            }
            $this->catalogues[$locale]->addCatalogue($this->loaders[$resource[0]]->load($resource[1], $locale, $resource[2]));
        }

        $this->optimizeCatalogue($locale);
    }

    protected function optimizeCatalogue($locale)
    {
        if (strlen($locale) > 3) {
            $fallback = substr($locale, 0, -strlen(strrchr($locale, '_')));
        } else {
            $fallback = $this->fallbackLocale;
        }

        if (!$fallback) {
            return;
        }

        if (!isset($this->catalogues[$fallback])) {
            $this->loadCatalogue($fallback);
        }

        $this->catalogues[$locale]->addFallbackCatalogue($this->catalogues[$fallback]);
    }
}
