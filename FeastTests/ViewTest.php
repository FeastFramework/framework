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

use Feast\Enums\DocType;
use Feast\Interfaces\ConfigInterface;
use Feast\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    protected View $view;

    /**
     * Set up test. Creates config and view.
     */
    public function setUp(): void
    {
        di(null,\Feast\Enums\ServiceContainer::CLEAR_CONTAINER);

        $config = $this->createStub(ConfigInterface::class);
        $config->method('getSetting')->will(
            $this->returnValueMap(
                [
                    ['siteurl', null, 'test'],
                    ['html.doctype', DocType::HTML_5->value, DocType::HTML_4_01_STRICT->value]
                ]
            )
        );

        $router = $this->createStub(\Feast\Interfaces\RouterInterface::class);
        $router->method('getPath')->will(
            $this->returnValueMap(
                [
                    ['feast', 'famine', [], [], null, 'Default', null, 'feast/famine']
                ]
            )
        );
        $this->view = new View($config, $router);
    }

    /**
     * @see View::getDtd
     */
    public function testGetDoctypeHtml401Strict(): void
    {
        $this->assertEquals(
            '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
            trim($this->view->getDtd())
        );
        $this->assertEquals(DocType::HTML_4_01_STRICT, $this->view->getDocType());
    }

    public function testGetDoctypeHtml5(): void
    {
        $this->view->setDoctype(DocType::HTML_5);

        $this->assertEquals(
            '<!DOCTYPE html>
<html>',
            trim($this->view->getDtd())
        );
        $this->assertEquals(DocType::HTML_5, $this->view->getDocType());
    }

    public function testGetDoctypeXhtml10Strict(): void
    {
        $this->view->setDoctype(DocType::XHTML_1_0_STRICT);
        $this->assertEquals(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">',
            trim($this->view->getDtd())
        );
        $this->assertEquals(DocType::XHTML_1_0_STRICT, $this->view->getDocType());
    }

    public function testGetDoctypeXhtml11(): void
    {
        $this->view->setDoctype(DocType::XHTML_1_1);
        $this->assertEquals(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
            trim($this->view->getDtd())
        );
        $this->assertEquals(DocType::XHTML_1_1, $this->view->getDocType());
    }

    public function testGetDoctypeXhtml10Transitional(): void
    {
        $this->view->setDoctype(DocType::XHTML_1_0_TRANSITIONAL);
        $this->assertEquals(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            trim($this->view->getDtd())
        );
        $this->assertEquals(DocType::XHTML_1_0_TRANSITIONAL, $this->view->getDocType());
    }

    public function testGetDoctypeHtml401Frameset(): void
    {
        $this->view->setDoctype(DocType::HTML_4_01_FRAMESET);
        $this->assertEquals(
            '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
            trim($this->view->getDtd())
        );
        $this->assertEquals(DocType::HTML_4_01_FRAMESET, $this->view->getDocType());
    }

    public function testGetDoctypeXhtml10Frameset(): void
    {
        $this->view->setDoctype(DocType::XHTML_1_0_FRAMESET);
        $this->assertEquals(
            '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
            trim($this->view->getDtd())
        );
        $this->assertEquals(DocType::XHTML_1_0_FRAMESET, $this->view->getDocType());
    }

    public function testGetDoctypeHtml401Transitional(): void
    {
        $this->view->setDoctype(DocType::HTML_4_01_TRANSITIONAL);
        $this->assertEquals(
            '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
            trim($this->view->getDtd())
        );
        $this->assertEquals(DocType::HTML_4_01_TRANSITIONAL, $this->view->getDocType());
    }

    public function testSetInvalidDoctype(): void
    {
        $this->expectException(\TypeError::class);
        $this->view->setDoctype('This is bad data.');
    }

    /**
     * @see View::addPreScriptSnippet
     * @see View::getPreScripts
     */
    public function testAddPreScriptSnippet(): void
    {
        $this->view->addPreScriptSnippet('test');
        $this->assertEquals('<script type="text/javascript">test</script>', trim($this->view->getPreScripts()));
        $this->view->emptyView();
        $this->assertEquals(
            '',
            trim($this->view->getPreScripts())
        );
    }

    /**
     * @see View::enableLayout
     * @see View::layoutEnabled
     */
    public function testEnableLayout(): void
    {
        $this->view->enableLayout();
        $this->assertEquals(true, $this->view->layoutEnabled());
    }

    /**
     * @see View::addCssFile
     * @see View::getCss
     */
    public function testAddCss(): void
    {
        $this->view->addCssFile('test.css');
        $this->assertEquals(
            '<link rel="stylesheet" type="text/css" href="/css/test.css" />',
            trim($this->view->getCss())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addCssFile
     * @see View::getCss
     */
    public function testAddCssNoDuplicate(): void
    {
        $this->view->addCssFile('test.css');
        $this->view->addCssFile('test.css');
        $this->assertEquals(
            '<link rel="stylesheet" type="text/css" href="/css/test.css" />',
            trim($this->view->getCss())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::setEncoding
     * @see View::getEncodingHtml
     */
    public function testGetEncodingHtml(): void
    {
        $this->view->setEncoding('EN');
        $this->assertEquals(
            '<meta http-equiv="Content-type" content="text/html;charset=EN">',
            trim($this->view->getEncodingHtml())
        );
        $this->view->emptyView();
    }

    public function testGetEncodingXhtmlHtml(): void
    {
        $this->view->setDoctype(DocType::XHTML_1_0_STRICT);
        $this->view->setEncoding('EN');
        $this->assertEquals(
            '<meta http-equiv="Content-type" content="text/html;charset=EN" />',
            trim($this->view->getEncodingHtml())
        );
        $this->view->emptyView();
    }

    public function testGetEncodingHtml5Html(): void
    {
        $this->view->setDoctype(DocType::HTML_5);
        $this->view->setEncoding('EN');
        $this->assertEquals(
            '<meta charset="EN">',
            trim($this->view->getEncodingHtml())
        );
        $this->view->emptyView();
    }

    public function testSetDocTypeGibberish(): void
    {
        $this->expectException(TypeError::class);
        $this->view->setDoctype('FeastHtml');
    }

    /**
     * @see View::disableOutput
     * @see View::outputDisabled
     */
    public function testDisableOutput(): void
    {
        $this->view->disableOutput();
        $this->assertEquals(true, $this->view->outputDisabled());
        $this->view->emptyView();
    }

    /**
     * @see View::disableLayout
     * @see View::layoutDisabled
     */
    public function testDisableLayout(): void
    {
        $this->view->disableLayout();
        $this->assertEquals(true, $this->view->layoutDisabled());
        $this->view->emptyView();
    }

    /**
     * @see View::enableOutput
     * @see View::outputEnabled
     */
    public function testEnableOutput(): void
    {
        $this->view->enableOutput();
        $this->assertEquals(true, $this->view->outputEnabled());
        $this->view->emptyView();
    }

    /**
     * @see View::setLayoutFile
     * @see View::emptyView
     * @see View::getLayoutFile
     */
    public function testSetLayoutFile(): void
    {
        $this->view->setLayoutFile('test');
        $this->assertEquals('test', $this->view->getLayoutFile());
        $this->view->emptyView();
        $this->assertNotEquals('test', $this->view->getLayoutFile());
    }

    /**
     * @see View::addPostScript
     * @see View::getPostScripts
     * @see View::emptyView
     */
    public function testAddPostScript(): void
    {
        $this->view->addPostScript('test.js');
        $this->view->addPostScript('test.js');
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>',
            trim($this->view->getPostScripts())
        );
        $this->view->emptyView();
        $this->assertEquals(
            '',
            trim($this->view->getPostScripts())
        );
    }

    /**
     * @see View::addPostScript
     * @see View::getPostScripts
     */
    public function testAddPostScriptAllowMulti(): void
    {
        $this->view->addPostScript('test.js');
        $this->view->addPostScript('test.js', false);
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>' . "\n" . '<script type="text/javascript" src="/js/test.js"></script>',
            trim($this->view->getPostScripts())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addCssFiles
     * @see View::getCss
     */
    public function testAddCssFiles(): void
    {
        $this->view->addCssFiles(
            [
                'test.css',
                'test2.css'
            ]
        );
        $this->assertEquals(
            '<link rel="stylesheet" type="text/css" href="/css/test.css" />' . "\n" . '<link rel="stylesheet" type="text/css" href="/css/test2.css" />',
            trim($this->view->getCss())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addCssFiles
     * @see View::getCss
     */
    public function testAddCssFilesNoDuplicates(): void
    {
        $this->view->addCssFiles(
            [
                'test.css',
                'test2.css',
                'test2.css'
            ]
        );
        $this->assertEquals(
            '<link rel="stylesheet" type="text/css" href="/css/test.css" />' . "\n" . '<link rel="stylesheet" type="text/css" href="/css/test2.css" />',
            trim($this->view->getCss())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addPreScript
     * @see View::getPreScripts
     */
    public function testAddPreScript(): void
    {
        $this->view->addPreScript('test.js');
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>',
            trim($this->view->getPreScripts())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addPreScript
     * @see View::getPreScripts
     */
    public function testAddPreScriptDisallowMulti(): void
    {
        $this->view->addPreScript('test.js');
        $this->view->addPreScript('test.js');
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>',
            trim($this->view->getPreScripts())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addPreScript
     * @see View::getPreScripts
     */
    public function testAddPreScriptAllowMulti(): void
    {
        $this->view->addPreScript('test.js');
        $this->view->addPreScript('test.js', false);
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>' . "\n" . '<script type="text/javascript" src="/js/test.js"></script>',
            trim($this->view->getPreScripts())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addPostScript
     * @see View::getPostScripts
     */
    public function testAddPostScripts(): void
    {
        $this->view->addPostScripts(
            [
                'test.js',
                'test2.js'
            ]
        );
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>' . "\n" . '<script type="text/javascript" src="/js/test2.js"></script>',
            trim($this->view->getPostScripts())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addPostScript
     * @see View::getPostScripts
     */
    public function testAddPostScriptsDisallowMulti(): void
    {
        $this->view->addPostScripts(
            [
                'test.js',
                'test2.js',
                'test2.js'
            ]
        );
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>' . "\n" . '<script type="text/javascript" src="/js/test2.js"></script>',
            trim($this->view->getPostScripts())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addPostScript
     * @see View::getPostScripts
     */
    public function testAddPostScriptsAllowMulti(): void
    {
        $this->view->addPostScripts(
            [
                'test.js',
                'test2.js',
                'test2.js'
            ],
            false
        );
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>' . "\n" . '<script type="text/javascript" src="/js/test2.js"></script>' . "\n" . '<script type="text/javascript" src="/js/test2.js"></script>',
            trim($this->view->getPostScripts())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::emptyView
     */
    public function testEmptyView(): void
    {
        $this->view->addCssFile('test');
        $this->view->emptyView();
        $this->assertEquals('', $this->view->getCss());
    }

    /**
     * @see View::setTitle
     * @see View::getTitle
     */
    public function testGetTitle(): void
    {
        $this->view->setTitle('test');
        $this->assertEquals('<title>test</title>', trim($this->view->getTitle()));
        $this->assertEquals('test', trim($this->view->getTitle(false)));
    }

    /**
     * @see View::addPreScript
     * @see View::getPreScripts
     */
    public function testAddPreScripts(): void
    {
        $this->view->addPreScripts(
            [
                'test.js',
                'test2.js'
            ]
        );
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>' . "\n" . '<script type="text/javascript" src="/js/test2.js"></script>',
            trim($this->view->getPreScripts())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addPreScript
     * @see View::getPreScripts
     */
    public function testAddPreScriptsDisallowMulti(): void
    {
        $this->view->addPreScripts(
            [
                'test.js',
                'test2.js',
                'test2.js'
            ]
        );
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>' . "\n" . '<script type="text/javascript" src="/js/test2.js"></script>',
            trim($this->view->getPreScripts())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addPreScript
     * @see View::getPreScripts
     */
    public function testAddPreScriptsAllowMulti(): void
    {
        $this->view->addPreScripts(
            [
                'test.js',
                'test2.js',
                'test2.js'
            ],
            false
        );
        $this->assertEquals(
            '<script type="text/javascript" src="/js/test.js"></script>' . "\n" . '<script type="text/javascript" src="/js/test2.js"></script>' . "\n" . '<script type="text/javascript" src="/js/test2.js"></script>',
            trim($this->view->getPreScripts())
        );
        $this->view->emptyView();
    }

    /**
     * @see View::addPostScriptSnippet
     * @see View::getPostScripts
     */
    public function testAddPostScriptSnippet(): void
    {
        $this->view->addPostScriptSnippet('test');
        $this->assertEquals('<script type="text/javascript">test</script>', trim($this->view->getPostScripts()));
        $this->view->emptyView();
        $this->assertEquals(
            '',
            trim($this->view->getPostScripts())
        );
    }

    public function testSetAndGet(): void
    {
        $this->view->__set('test', 'ing');
        $this->assertEquals('ing', $this->view->__get('test'));
    }

    public function testShowViewWithLayout(): void
    {
        $this->expectOutputString(
            'This is a layout.
This file is used by ViewTest.php to ensure the view class is behaving correctly.'
        );
        $this->view->showView('Test', 'Test');
    }

    public function testShowViewWithoutLayout(): void
    {
        $this->expectOutputString('This file is used by ViewTest.php to ensure the view class is behaving correctly.');
        $this->view->disableLayout();
        $this->view->showView('Test', 'Test');
    }

    public function testPartial(): void
    {
        $this->expectOutputString('This file is used by ViewTest.php to ensure the view class is behaving correctly.');
        $this->view->item = 'test';
        $this->view->partial('Test/Test.phtml', ['test' => 'test']);
    }

    public function testPartialLoop2Loops(): void
    {
        $this->expectOutputString(
            'This file is used by ViewTest.php to ensure the view class is behaving correctly.This file is used by ViewTest.php to ensure the view class is behaving correctly.'
        );
        $this->view->partialLoop('Test/Test', [['1'], ['2']]);
    }

    public function testPartialLoop3Loops(): void
    {
        $this->expectOutputString(
            'This file is used by ViewTest.php to ensure the view class is behaving correctly.This file is used by ViewTest.php to ensure the view class is behaving correctly.This file is used by ViewTest.php to ensure the view class is behaving correctly.'
        );
        $this->view->partialLoop('Test/Test', [['1'], ['2'], ['3']]);
    }

    public function testUrl(): void
    {
        $url = $this->view->url('feast', 'famine');
        self::assertEquals('/feast/famine', $url);
    }

    public function testShowViewNoOutput(): void
    {
        $this->expectOutputString('');
        $this->view->disableOutput();
        $this->view->showView('Test', 'Test');
    }

    public function testGetEncoding(): void
    {
        $encoding = $this->view->getEncoding();
        $this->assertEquals('UTF-8', $encoding);
    }

}
