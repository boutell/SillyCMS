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
 * CustomFilterIterator filters files by applying anonymous functions.
 *
 * The anonymous function receives a \SplFileInfo and must return false
 * to remove files.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class CustomFilterIterator extends \FilterIterator
{
    protected $filters = array();

    /**
     * Constructor.
     *
     * @param \Iterator $iterator The Iterator to filter
     * @param array     $filters  An array of \Closure
     */
    public function __construct(\Iterator $iterator, array $filters)
    {
        $this->filters = $filters;

        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return Boolean true if the value should be kept, false otherwise
     */
    public function accept()
    {
        $fileinfo = $this->getInnerIterator()->current();

        foreach ($this->filters as $filter) {
            if (false === $filter($fileinfo)) {
                return false;
            }
        }

        return true;
    }
}
