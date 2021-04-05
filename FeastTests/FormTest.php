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

use Feast\Form\Field\Text;
use Feast\Form\Form;
use Feast\Form\Validator\Alphabetical;
use Feast\Form\Validator\AlphaNumeric;
use Feast\Form\Validator\AlphaNumericSpaces;
use Feast\Form\Validator\Decimal;
use Feast\Form\Validator\Email;
use Feast\Form\Validator\File;
use Feast\Form\Validator\Image;
use Feast\Form\Validator\Numeric;
use Feast\Form\Validator\Url;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    public function testTextBox(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $form->addField(new Text('test'));
        $this->assertEquals('<input type="text" name="test" id="TestForm_test" />', $form->displayField('test'));
    }

   
    public function testDisplayInvalidField(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $this->expectException(\Feast\Exception\InvalidArgumentException::class);
        $form->displayField('test');
    }

    public function testGetInvalidField(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $this->expectException(\Feast\Exception\InvalidArgumentException::class);
        $form->getField('test');
    }

    public function testSetAction(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test', 'GET', ['test' => 'testing']]);
        $form->setAction('/test2');
        $this->assertEquals('<form id="TestForm" action="/test2" method="GET" test="testing">', $form->openForm());
    }

    public function testValidate(): void
    {
        $form = $this->getValidationForm();

        $_FILES = [
            'image' => ['error' => 0, 'tmp_name' => 'success'],
            'file' => ['error' => 0]
        ];
        $form->setAllFiles();
        $form->setAllValues([
                                'required' => 'test',
                                'alphabetical' => 'test',
                                'numeric' => '123',
                                'alphanumeric' => 'abc123',
                                'email' => 'test@example.com',
                                'array' => 'on',
                                'image' => 'test',
                                'file' => 'filename',
                                'url' => 'http://www.google.com',
                                'decimal' => '123.45',
                                'alphanumericspaces' => 'abc 123'
                            ]);
        $valid = $form->isValid();
        $this->assertTrue($valid);
    }

    public function testValidateInvalid(): void
    {
        $form = $this->getValidationForm();

        $_FILES = [
            'image' => ['error' => 0, 'tmp_name' => 'failure'],
            'file' => ['error' => 1]
        ];
        $form->setAllFiles();
        $form->setAllValues([
                                'required' => '',
                                'alphabetical' => '123',
                                'numeric' => 'abc',
                                'alphanumeric' => '#^4',
                                'email' => 'test',
                                'array' => 'off',
                                'image' => 'test',
                                'file' => 'filename',
                                'url' => 'httpdxpotato',
                                'decimal' => 'a345',
                                'alphanumericspaces' => '#3q4%'
                            ]);
        $valid = $form->isValid();
        $this->assertFalse($valid);
        $this->assertCount(11, $form->getErrors());
    }

    public function testValidateInvalidPartial(): void
    {
        $form = $this->getValidationForm();

        $_FILES = [
            'image' => ['error' => 0, 'tmp_name' => 'failure'],
            'file' => ['error' => 1]
        ];
        $form->setAllFiles();
        $form->setAllValues([
                                'required' => '',
                                'requiredAlt' => '',
                                'alphabetical' => '123',
                                'numeric' => 'abc',
                                'alphanumeric' => '#^4',
                                'email' => 'test',
                                'array' => 'off',
                                'image' => 'test',
                                'file' => 'filename',
                                'url' => 'httpdxpotato',
                                'decimal' => 'a345',
                                'alphanumericspaces' => '#3q4%'
                            ]);
        $valid = $form->isValidPartial();
        $this->assertFalse($valid);
        $this->assertCount(9, $form->getErrors());
    }

    public function testGetFile(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);

        $fieldFile = new Text('test', 'test', 'text');
        $form->addField($fieldFile);

        $_FILES = [
            'file' => ['error' => 1]
        ];
        $form->setAllFiles();

        $this->assertNotNull($form->getFile('file'));
    }

    public function testFilterTrim(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $formField = new Text('test');
        $formField->addFilter(\Feast\Form\Filter\Trim::class);
        $form->addField($formField);
        $items = new stdClass();
        $items->test = ' feast ';
        $form->filter((array)$items);
        $field = $form->getField('test');
        $this->assertEquals('feast', $field->value);
    }

    public function testFilterMd5(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $formField = new Text('test');
        $formField->addFilter(\Feast\Form\Filter\Md5::class);
        $form->addField($formField);
        $items = new stdClass();
        $items->test = 'feast';
        $form->filter((array)$items);
        $field = $form->getField('test');
        $this->assertEquals('45870f56e9bb21fe9cedd76eb66cfca7', $field->value);
    }

    public function testFilterSha1(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $formField = new Text('test');
        $formField->addFilter(\Feast\Form\Filter\Sha1::class);
        $form->addField($formField);
        $items = new stdClass();
        $items->test = 'feast';
        $form->filter((array)$items);
        $field = $form->getField('test');
        $this->assertEquals('f37a74d82869756054661d6501b29cdfec0fdb38', bin2hex($field->value));
    }

    public function testFilterSha1NoValue(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $formField = new Text('test');
        $formField->addFilter(\Feast\Form\Filter\Sha1::class);
        $form->addField($formField);
        $items = new stdClass();
        $form->filter((array)$items);
        $field = $form->getField('test');
        $this->assertEquals('', $field->value);
    }

    public function testCloseForm(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $this->assertEquals('</form>', $form->closeForm());
    }

    public function testSetValue(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $formField = new Text('test');
        $form->addField($formField);
        $form->setValue('test', 'feast');
        $field = $form->getField('test');
        $this->assertEquals('feast', $field->value);
    }

    public function testSetValueInvalid(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $this->expectException(\Feast\Exception\InvalidArgumentException::class);
        $form->setValue('test', 'feast');
    }

    public function testSetAllValues(): void
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $formField = new Text('test');
        $formField->addFilter(\Feast\Form\Filter\Sha1::class);
        $form->addField($formField);
        $formField = new \Feast\Form\Field\Select('test2');
        $formField->addValue('on',new \Feast\Form\Field\Value('on'));
        $formField->addValue('test',new \Feast\Form\Field\Value('test'));
        $formField->addValue('ing',new \Feast\Form\Field\Value('ing'));
        $form->addField(
            $formField
        );
        $form->setAllValues(['test' => 'feast', 'potato' => 'test', 'test2' => ['test', 'ing']]);
        $field = $form->getField('test');
        $this->assertEquals('feast', $field->value);
        $field = $form->getField('test2');
        $values = [];
        foreach ($field->values as $value) {
            if ($value->selected === true) {
                $values[] = $value->value;
            }
        }
        $this->assertEquals(['test', 'ing'], $values);
    }

    protected function getValidationForm(): Form|\PHPUnit\Framework\MockObject\MockObject
    {
        $form = $this->getMockForAbstractClass(Form::class, ['TestForm', '/test']);
        $fieldNotRequired = new Text('notrequired', 'test', 'text');
        $fieldRequired = new Text('required', 'test', 'text');
        $fieldRequired->setRequired();
        $fieldAlphabetical = new Text('alphabetical', 'test', 'text');
        $fieldAlphabetical->addValidator(Alphabetical::class);
        $fieldNumeric = new Text('numeric', 'test', 'text');
        $fieldNumeric->addValidator(Numeric::class);
        $fieldAlphaNumeric = new Text('alphanumeric', 'test', 'text');
        $fieldAlphaNumeric->addValidator(AlphaNumeric::class);
        $fieldRadio = new \Feast\Form\Field\Radio('array', 'test');
        $fieldRadio->addValue('on', new \Feast\Form\Field\Value('on'));
        $fieldRadio->setRequired();
        $fieldEmail = new Text('email', 'test', 'text');
        $fieldEmail->addValidator(Email::class);
        $fieldImage = new Text('image', 'test', 'text');
        $fieldImage->addValidator(Image::class);
        $fieldFile = new Text('file', 'test', 'text');
        $fieldFile->addValidator(File::class);
        $fieldUrl = new Text('url', 'test', 'text');
        $fieldUrl->addValidator(Url::class);
        $fieldDecimal = new Text('decimal', 'test', 'text');
        $fieldDecimal->addValidator(Decimal::class);
        $fieldAlphaNumericSpaces = new Text('alphanumericspaces', 'test', 'text');
        $fieldAlphaNumericSpaces->addValidator(AlphaNumericSpaces::class);
        $form->addField($fieldNotRequired);
        $form->addField($fieldRequired);
        $form->addField($fieldAlphabetical);
        $form->addField($fieldNumeric);
        $form->addField($fieldAlphaNumeric);
        $form->addField($fieldRadio);
        $form->addField($fieldEmail);
        $form->addField($fieldImage);
        $form->addField($fieldFile);
        $form->addField($fieldUrl);
        $form->addField($fieldDecimal);
        $form->addField($fieldAlphaNumericSpaces);
        return $form;
    }
}
