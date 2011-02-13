<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Security\Acl\Model;

/**
 * Interface for entries which are restricted to specific fields
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface FieldAwareEntryInterface
{
    function getField();
}