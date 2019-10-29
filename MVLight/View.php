<?php

/**
 *  MIT License
 *
 *  Copyright (c) 2019 Cosmin Fane Cozma cozmacosmin1992@gmail.com
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace MVLight\Views;

use InvalidArgumentException;
use MVLight\View\Exception\LayoutNotFoundException;
use MVLight\View\Exception\TemplateNotFoundException;
use Throwable;


/**
 * Class View
 *
 * Class representing the presentation layer.
 *
 * @package MVLight\Views
 */
class View
{
    /**
     * Path to the root directory containing all views
     *
     * @var string
     */
    protected $templatePath;

    /**
     * Global attributes of the application
     *
     * @var array
     */
    protected $attributes;

    /**
     * path to the layout af the view
     *
     * @var string
     */
    protected $layout;

    /**
     * @__construct
     *
     * @param string $templatePath
     * @param array $attributes
     * @param string $layout
     */
    public function __construct($templatePath = '', $attributes = [], $layout = '')
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';
        $this->attributes = $attributes;
        $this->setLayout($layout);
    }

    /**
     * @param string $template
     * @param array $data
     *
     * @return string
     *
     * @throws Throwable
     */
    public function render($template, $data = [])
    {
        return $this->fetch($template, $data, true);
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     */
    public function setLayout($layout)
    {
        if ($layout === '' || $layout === null) {
            $this->layout = null;
        } else {
            $layoutPath = $this->templatePath . $layout;
            if (!is_file($layoutPath)) {
                throw new LayoutNotFoundException('Layout template "' . $layout . '" does not exist');
            }
            $this->layout = $layout;
        }
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     *
     * @return void
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return void
     */
    public function addAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return bool|mixed
     */
    public function getAttribute($key)
    {
        if (!isset($this->attributes[$key])) {
            return false;
        }
        return $this->attributes[$key];
    }

    /**
     * @return string
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    /**
     * @param string $templatePath
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';
    }

    /**
     * @param string $template
     * @param array $data
     * @param bool $useLayout
     *
     * @return string
     *
     * @throws Throwable
     */
    public function fetch($template, $data = [], $useLayout = false)
    {
        $output = $this->fetchTemplate($template, $data);
        if ($this->layout !== null && $useLayout) {
            $data['content'] = $output;
            $output = $this->fetchTemplate($this->layout, $data);
        }
        return $output;
    }

    /**
     * @param string $template
     * @param array $data
     *
     * @return string
     *
     * @throws Throwable
     */
    public function fetchTemplate($template, $data = [])
    {
        if (isset($data['template'])) {
            throw new InvalidArgumentException('Duplicate template key found');
        }
        if (!is_file($this->templatePath . $template)) {
            throw new TemplateNotFoundException('View cannot render "' . $template . '" because the template does not exist');
        }
        $data = array_merge($this->attributes, $data);
        try {
            ob_start();
            $this->protectedIncludeScope($this->templatePath . $template, $data);
            $output = ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return $output;
    }

    /**
     * @param string $template
     * @param array $data
     *
     * @return void
     */
    protected function protectedIncludeScope($template, $data)
    {
        extract($data);
        include $template;
    }
}