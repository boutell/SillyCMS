<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Finder\Iterator;

/**
 * ExcludeDirectoryFilterIterator filters out directories.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class ExcludeDirectoryFilterIterator extends \FilterIterator
{
    protected $patterns;

    /**
     * Constructor.
     *
     * @param \Iterator $iterator    The Iterator to filter
     * @param array     $directories An array of directories to exclude
     */
    public function __construct(\Iterator $iterator, array $directories)
    {
        $this->patterns = array();
        foreach ($directories as $directory) {
            $this->patterns[] = '#(^|/)'.preg_quote($directory, '#').'(/|$)#';
        }

        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return Boolean true if the value should be kept, false otherwise
     */
    public function accept()
    {
        $inner = $this;
        while ($inner && !$inner->getInnerIterator() instanceof \RecursiveIteratorIterator) {
            $inner = $inner->getInnerIterator();
        }

        $method = $inner->current()->isDir() ? 'getSubPathname' : 'getSubPath';
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, strtr($this->getInnerIterator()->$method(), '\\', '/'))) {
                return false;
            }
        }

        return true;
    }
}
