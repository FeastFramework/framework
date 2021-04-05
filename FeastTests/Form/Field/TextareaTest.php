<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);

namespace Form\Field;

use Feast\Form\Field;
use Feast\Form\Field\Textarea;
use Feast\Form\Label;
use PHPUnit\Framework\TestCase;

class TextareaTest extends TestCase
{
    public function testCreate(): void
    {
        $text = $this->getTextAreaObject();
        $this->assertTrue($text instanceof Textarea);
    }

    public function testSetValue(): void
    {
        $text = $this->getTextAreaObject();
        $text->setValue('Feast');

        $this->assertTrue($text->value === 'Feast');
    }

    public function testClearSelected(): void
    {
        $text = $this->getTextAreaObject();
        $text->setValue('Feast');
        $text->clearSelected();
        $this->assertTrue($text->value === '');
    }

    public function testToStringLabelFirst(): void
    {
        $text = $this->getTextAreaObject();
        $text->setValue('Feast');

        $this->assertEquals(
            '<label>testing</label><textarea name="test" class="testClass" id="testId" placeholder="enter text" style="color: #fff" required="required">Feast</textarea>',
            $text->toString()
        );
    }

    public function testToStringLabelLast(): void
    {
        $text = $this->getTextAreaObject();
        $text->setValue('Feast');
        $text->label->setPosition(Label::LABEL_POSITION_LAST);
        $this->assertEquals(
            '<textarea name="test" class="testClass" id="testId" placeholder="enter text" style="color: #fff" required="required">Feast</textarea><label>testing</label>',
            $text->toString()
        );
    }

    public function testToStringWithMetaLabelLast(): void
    {
        $text = $this->getTextAreaObject();
        $text->setValue('Feast')->addMeta('test','test');
        $text->label->setPosition(Label::LABEL_POSITION_LAST);
        $this->assertEquals(
            '<textarea name="test" class="testClass" id="testId" placeholder="enter text" style="color: #fff" required="required" test="test">Feast</textarea><label>testing</label>',
            $text->toString()
        );
    }
    
    public function testSetDefault(): void
    {
        $text = $this->getTextAreaObject();
        $text->setDefault('test');
        $this->assertEquals('test',$text->default);
    }

    /**
     * @return TextArea
     */
    protected function getTextAreaObject(): TextArea
    {
        $textArea = new Textarea(
            'test', 'formTest', 'testId'
        );
        $label = new Label('testing');
        $textArea//->addMeta('test', 'test')
            ->setClass('testClass')
            ->setPlaceholder('enter text')
            ->setStyle('color: #fff')
            ->setLabel($label)
            ->setRequired();
        return $textArea;
    }
}
