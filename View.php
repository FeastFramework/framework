<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Feast;

use Exception;
use Feast\Collection\Collection;
use Feast\Enums\DocTypes;
use Feast\Interfaces\ConfigInterface;
use Feast\Interfaces\RouterInterface;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;
use stdClass;

/**
 * Manages the rendering of views or json views as requested.
 */
// Extends stdClass for type hint assistance
class View extends stdClass implements ServiceContainerItemInterface
{
    use DependencyInjected;

    private string $baseUrl;
    /** @var array<string> */
    private array $css = [];
    private bool $outputDisabled = false;
    private string $content = '';
    private string $docType = DocTypes::HTML_5;
    private string $dtd = '<!DOCTYPE html>' . "\n" . '<html>';
    private string $encoding = 'UTF-8';
    private array $postScripts = [];
    private array $preScripts = [];
    private array $postScriptSnippet = [];
    private array $preScriptSnippet = [];
    private bool $showLayout = true;
    private string $layoutFile = 'layout';
    private string $title = '';

    /**
     * @param ConfigInterface $config
     * @param RouterInterface $router
     * @throws ServiceContainer\NotFoundException|ServiceContainer\ContainerException
     * @throws Exception
     */
    public function __construct(protected ConfigInterface $config, protected RouterInterface $router)
    {
        $this->checkInjected();
        $this->baseUrl = (string)$config->getSetting('siteurl');
        $docType = (string)$config->getSetting('html.doctype', DocTypes::HTML_5);
        $this->setDoctype($docType);
    }

    /**
     * Outputs the view to the browser.
     *
     * If layout is not disabled, includes the layout file first.
     *
     * @param string $controller
     * @param string $action
     * @param string $route = ''
     */
    public function showView(string $controller, string $action, string $route = ''): void
    {
        if ($this->outputDisabled) {
            return;
        }
        $content = APPLICATION_ROOT . $route . 'Views/' . $controller . '/' . $action . '.phtml';
        if ($this->showLayout) {
            /** @psalm-suppress UnresolvableInclude */
            include(APPLICATION_ROOT . $route . 'Views/' . $this->layoutFile . '.phtml');
        } else {
            /** @psalm-suppress UnresolvableInclude */
            include($content);
        }
    }

    /**
     * Disable view output.
     */
    public function disableOutput(): void
    {
        $this->outputDisabled = true;
    }

    /**
     * Enable view output.
     */
    public function enableOutput(): void
    {
        $this->outputDisabled = false;
    }

    /**
     * Check if output is disabled.
     *
     * @return bool
     */
    public function outputDisabled(): bool
    {
        return $this->outputDisabled;
    }

    /**
     * Check if output is enabled.
     *
     * @return bool
     */
    public function outputEnabled(): bool
    {
        return !$this->outputDisabled();
    }

    /**
     * Disable the view layout.
     */
    public function disableLayout(): void
    {
        $this->showLayout = false;
    }

    /**
     * Enable the view layout.
     */
    public function enableLayout(): void
    {
        $this->showLayout = true;
    }

    /**
     * Check if layout view is enabled.
     *
     * @return bool
     */
    public function layoutEnabled(): bool
    {
        return $this->showLayout;
    }

    /**
     * Check if layout view is disabled.
     *
     * @return bool
     */
    public function layoutDisabled(): bool
    {
        return !$this->layoutEnabled();
    }

    /**
     * Returns all the preScripts as script tags.
     *
     * @return string
     */
    public function getPreScripts(): string
    {
        $allScripts = '';
        /** @var string $script */
        foreach ($this->preScripts as $script) {
            $filename = str_starts_with($script, 'http') ? $script : '/js/' . $script;
            $allScripts .= '<script type="text/javascript" src="' . $filename . '"></script>' . PHP_EOL;
        }
        /** @var string $script */
        foreach ($this->preScriptSnippet as $script) {
            $allScripts .= '<script type="text/javascript">' . $script . '</script>' . PHP_EOL;
        }

        return $allScripts;
    }

    /**
     * Adds a javascript file for the <head> tag.
     *
     * Feast uses the /js/ folder for the base directory of all scripts.
     * If onlyOne is true, ensures the script is only loaded once.
     *
     * @param string $fileName
     * @param bool $onlyOne
     */
    public function addPreScript(string $fileName, bool $onlyOne = true): void
    {
        if (!$onlyOne || !in_array($fileName, $this->preScripts)) {
            $this->preScripts[] = $fileName;
        }
    }

    /**
     * Adds multiple javascript file for the <head> tag.
     *
     * Feast uses the /js/ folder for the base directory of all scripts.
     * If onlyOne is true, ensures the scripts are each only loaded once.
     *
     * @param array<string> $fileNames
     * @param bool $onlyOne
     */
    public function addPreScripts(array $fileNames, bool $onlyOne = true): void
    {
        foreach ($fileNames as $fileName) {
            $this->addPreScript($fileName, $onlyOne);
        }
    }

    /**
     * Adds a JavaScript snippet for the <head> tag.
     *
     * @param string $snippet
     */
    public function addPreScriptSnippet(string $snippet): void
    {
        $this->preScriptSnippet[] = $snippet;
    }

    /**
     * Returns all the end-of-body scripts as script tags
     *
     * @return string
     */
    public function getPostScripts(): string
    {
        $allScripts = '';
        /** @var string $script */
        foreach ($this->postScripts as $script) {
            $filename = str_starts_with($script, 'http') ? $script : '/js/' . $script;
            $allScripts .= '<script type="text/javascript" src="' . $filename . '"></script>' . PHP_EOL;
        }
        /** @var string $script */
        foreach ($this->postScriptSnippet as $script) {
            $allScripts .= '<script type="text/javascript">' . $script . '</script>' . PHP_EOL;
        }

        return $allScripts;
    }

    /**
     * Adds a javascript file for the end of the <body> tag.
     *
     * Feast uses the /js/ folder for the base directory of all scripts.
     * If onlyOne is true, ensures the script is only loaded once.
     *
     * @param string $fileName
     * @param bool $onlyOne
     */
    public function addPostScript(string $fileName, bool $onlyOne = true): void
    {
        if (!$onlyOne || !in_array($fileName, $this->postScripts)) {
            $this->postScripts[] = $fileName;
        }
    }

    /**
     * Adds multiple javascript files for the end of <body> tag.
     *
     * Feast uses the /js/ folder for the base directory of all scripts.
     * If onlyOne is true, ensures the scripts are each only loaded once.
     *
     * @param array<string> $fileNames
     * @param bool $onlyOne
     */
    public function addPostScripts(array $fileNames, bool $onlyOne = true): void
    {
        foreach ($fileNames as $fileName) {
            $this->addPostScript($fileName, $onlyOne);
        }
    }

    /**
     * Adds a JavaScript snippet for the end of <body> tag.
     *
     * @param string $snippet
     */
    public function addPostScriptSnippet(string $snippet): void
    {
        $this->postScriptSnippet[] = $snippet;
    }

    /**
     * Gets all the css style sheets that have been loaded as html link tags.
     *
     * @return string
     */
    public function getCss(): string
    {
        $allCss = '';
        foreach ($this->css as $css) {
            $allCss .= '<link rel="stylesheet" type="text/css" href="/css/' . $css . '" />' . PHP_EOL;
        }

        return $allCss;
    }

    /**
     * Adds a css file to the page,
     * 
     * Feast uses the /css/ folder for the base directory for all stylesheets.
     *
     * @param string $fileName
     */
    public function addCssFile(string $fileName): void
    {
        if (!in_array($fileName, $this->css)) {
            $this->css[] = $fileName;
        }
    }

    /**
     * Adds multiple css file to the page,
     *
     * Feast uses the /css/ folder for the base directory for all stylesheets.
     *
     * @param array<string> $fileNames
     */
    public function addCssFiles(array $fileNames): void
    {
        foreach ($fileNames as $fileName) {
            $this->addCssFile($fileName);
        }
    }

    /**
     * Add or change the page title.
     *
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Returns the page title.
     *
     * @param bool $html
     * @return string
     */
    public function getTitle(bool $html = true): string
    {
        if ($html) {
            return '<title>' . $this->title . '</title>' . PHP_EOL;
        }

        return $this->title;
    }

    /**
     * Sets the doc type of the page.
     * 
     * Also sets the doctype declaration
     *
     * @param string $doctype
     * @throws Exception
     */
    public function setDoctype(string $doctype = DocTypes::HTML_5): void
    {
        if ($doctype == DocTypes::HTML_4_01_TRANSITIONAL) {
            $this->dtd = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
        } elseif ($doctype == DocTypes::HTML_4_01_STRICT) {
            $this->dtd = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
        } elseif ($doctype == DocTypes::HTML_4_01_FRAMESET) {
            $this->dtd = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
        } elseif ($doctype == DocTypes::HTML_5) {
            $this->dtd = '<!DOCTYPE html>' . "\n" . '<html>';
        } elseif ($doctype == DocTypes::XHTML_1_0_TRANSITIONAL) {
            $this->dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        } elseif ($doctype == DocTypes::XHTML_1_0_STRICT) {
            $this->dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">';
        } elseif ($doctype == DocTypes::XHTML_1_0_FRAMESET) {
            $this->dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
        } elseif ($doctype == DocTypes::XHTML_1_1) {
            $this->dtd = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
        } else {
            throw new Exception('Invalid doctype');
        }
        $this->docType = $doctype;
    }

    /**
     * Get the Doctype Declaration.
     * 
     * @return string
     */
    public function getDtd(): string
    {
        return $this->dtd . PHP_EOL;
    }

    /**
     * Get the Doctype.
     * 
     * @return string
     */
    public function getDocType(): string
    {
        return $this->docType;
    }

    /**
     * Set the encoding for the page.
     * 
     * Default is UTF-8.
     *
     * @param string $encoding
     */
    public function setEncoding(string $encoding = 'UTF-8'): void
    {
        $this->encoding = $encoding;
    }

    /**
     * Returns the meta tag page encoding.
     *
     * @return string
     * @throws Exception
     */
    public function getEncodingHtml(): string
    {
        return match ($this->docType) {
            DocTypes::XHTML_1_0_STRICT, DocTypes::XHTML_1_0_TRANSITIONAL, DocTypes::XHTML_1_0_FRAMESET => '<meta http-equiv="Content-type" content="text/html;charset=' . $this->encoding . '" />' . PHP_EOL,
            DocTypes::HTML_4_01_TRANSITIONAL, DocTypes::HTML_4_01_FRAMESET, DocTypes::HTML_4_01_STRICT => '<meta http-equiv="Content-type" content="text/html;charset=' . $this->encoding . '">' . PHP_EOL,
            DocTypes::HTML_5 => '<meta charset="' . $this->encoding . '">' . PHP_EOL
        };
    }

    /**
     * Get the raw encoding.
     * 
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Render a partial view.
     * 
     * Any duplicate arguments will override ones in the view during the partial. 
     * The view itself does not change.
     *
     * @param string $file - filename to use for the view (in Views folder).
     * @param array $arguments - variables to be assigned onto the view.
     * @param bool $includeView - whether to load the view variables for the partial or not.
     */
    public function partial(string $file, array $arguments = [], bool $includeView = true): void
    {
        new Partial($file, $this, $arguments, includeView: $includeView);
    }

    /**
     * Render partial views by looping through the arguments loop. 
     *
     * Any duplicate arguments will override ones in the view during the partial.
     * The view itself does not change.
     *
     * @param string $file - filename to use for the view (in Views folder).
     * @param array<array> $arguments - an array of arrays of variables or a collection of arrays to be assigned onto the view.
     */
    public function partialLoop(string $file, array|Collection $arguments): void
    {
        foreach ($arguments as $key => $argument) {
            new Partial($file, $this, $argument, $key);
        }
    }

    /**
     * Get the url path.
     *
     * Designed for use in views to avoid having to call route class in view.
     *
     * @param string|null $action
     * @param string|null $controller
     * @param array<string> $arguments
     * @param array<string> $queryString
     * @param string|null $module
     * @param string $route
     * @param bool $fullPath
     * @param string|null $requestMethod
     * @return string
     * @throws Exception
     */
    public function url(
        ?string $action = null,
        ?string $controller = null,
        array $arguments = [],
        array $queryString = [],
        ?string $module = null,
        string $route = 'Default',
        bool $fullPath = false,
        ?string $requestMethod = null
    ): string {
        $path = (string)$this->config->getSetting('siteurl');

        return ($fullPath ? $path : '') . DIRECTORY_SEPARATOR . $this->router->getPath(
                $action,
                $controller,
                $arguments,
                $queryString,
                $module,
                $route,
                $requestMethod
            );
    }

    /**
     * Reset the view to be void of css or js, enable output, and enable layout.
     */
    public function emptyView(): void
    {
        $this->preScripts = [];
        $this->postScripts = [];
        $this->css = [];
        $this->preScriptSnippet = [];
        $this->postScriptSnippet = [];
        $this->outputDisabled = false;
        $this->showLayout = true;
        $this->setLayoutFile();
        $this->setEncoding();
        $this->setTitle('');
    }

    /**
     * Get layout filename.
     * 
     * @return string
     */
    public function getLayoutFile(): string
    {
        return $this->layoutFile;
    }

    /**
     * Set layout filename. 
     * @param string $file
     */
    public function setLayoutFile(string $file = 'layout'): void
    {
        $this->layoutFile = str_replace('.phtml', '', $file);
    }

    /**
     * Set a dynamic value on the view.
     * 
     * @param string $key
     * @param string|int|bool|float|object|array|null $value
     */
    public function __set(string $key, string|int|bool|float|object|null|array $value): void
    {
        $this->$key = $value;
    }

    /**
     * Get a dynamic value from the view.
     * 
     * @param string $value
     * @return mixed
     */
    public function __get(string $value): mixed
    {
        return $this->$value ?? null;
    }

}
