<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileBag is a container for HTTP headers.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class FileBag extends ParameterBag
{
    private $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');

    /**
     * Constructor.
     *
     * @param array $headers An array of HTTP files
     */
    public function __construct(array $parameters = array())
    {
        // this line is not necessary, but including it avoids any stupid
        // errors if we add code to the parent's constructor
        parent::__construct();

        $this->replace($parameters);
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\HttpFoundation\ParameterBag::replace()
     */
    public function replace(array $files = array())
    {
        $this->parameters = array();
        $this->add($files);
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\HttpFoundation\ParameterBag::set()
     */
    public function set($key, $value)
    {
        if (is_array($value) || $value instanceof UploadedFile) {
            parent::set($key, $this->convertFileInformation($value));
        }
    }

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\HttpFoundation\ParameterBag::add()
     */
    public function add(array $files = array())
    {
        foreach ($files as $key => $file) {
            $this->set($key, $file);
        }
    }

    /**
     * Converts uploaded files to UploadedFile instances.
     *
     * @param  array|UploadedFile $file A (multi-dimensional) array of uploaded file information
     *
     * @return array A (multi-dimensional) array of UploadedFile instances
     */
    protected function convertFileInformation($file)
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }
        $file = $this->fixPhpFilesArray($file);
        if (is_array($file)) {
            $keys = array_keys($file);
            sort($keys);
            if ($keys == $this->fileKeys) {
                $file['error'] = (int) $file['error'];
            }
            if ($keys != $this->fileKeys) {
                $file = array_map(array($this, 'convertFileInformation'), $file);
            } else
                if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                    $file = null;
                } else {
                    $file = new UploadedFile($file['tmp_name'], $file['name'],
                    $file['type'], $file['size'], $file['error']);
                }
        }
        return $file;
    }

    /**
     * Fixes a malformed PHP $_FILES array.
     *
     * PHP has a bug that the format of the $_FILES array differs, depending on
     * whether the uploaded file fields had normal field names or array-like
     * field names ("normal" vs. "parent[child]").
     *
     * This method fixes the array to look like the "normal" $_FILES array.
     *
     * It's safe to pass an already converted array, in which case this method
     * just returns the original array unmodified.
     *
     * @param  array $data
     * @return array
     */
    protected function fixPhpFilesArray($data)
    {
        if (! is_array($data)) {
            return $data;
        }
        $keys = array_keys($data);
        sort($keys);
        if ($this->fileKeys != $keys || ! isset($data['name']) ||
         ! is_array($data['name'])) {
            return $data;
        }
        $files = $data;
        foreach ($this->fileKeys as $k) {
            unset($files[$k]);
        }
        foreach (array_keys($data['name']) as $key) {
            $files[$key] = $this->fixPhpFilesArray(array(
                'error'    => $data['error'][$key],
                'name'     => $data['name'][$key], 'type' => $data['type'][$key],
                'tmp_name' => $data['tmp_name'][$key],
                'size'     => $data['size'][$key]
            ));
        }
        return $files;
    }
}
