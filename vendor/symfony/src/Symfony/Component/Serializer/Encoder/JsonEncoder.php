<?php

namespace Symfony\Component\Serializer\Encoder;

use Symfony\Component\Serializer\SerializerInterface;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Encodes JSON data
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class JsonEncoder extends AbstractEncoder
{
    /**
     * {@inheritdoc}
     */
    public function encode($data, $format)
    {
        if ($this->serializer->isStructuredType($data)) {
            $data = $this->serializer->normalize($data, $format);
        }
        return json_encode($data);
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format)
    {
        return json_decode($data, true);
    }
}
