<?php

namespace Fiachehr\LaravelEav\Tests\Unit\Services;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Enums\ValidationType;
use Fiachehr\LaravelEav\Domain\Services\AttributeValidator;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class AttributeValidatorTest extends TestCase
{
    protected AttributeValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new AttributeValidator();
    }

    /** @test */
    public function it_converts_required_validation_to_laravel_rule(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::TEXT,
            ['required']
        );

        $this->assertContains('required', $rules);
    }

    /** @test */
    public function it_converts_min_length_validation_with_parameter(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::TEXT,
            [['type' => 'min_length', 'parameter' => 5]]
        );

        $this->assertContains('min:5', $rules);
    }

    /** @test */
    public function it_converts_max_length_validation_with_parameter(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::TEXT,
            [['type' => 'max_length', 'parameter' => 100]]
        );

        $this->assertContains('max:100', $rules);
    }

    /** @test */
    public function it_converts_email_validation(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::TEXT,
            ['email']
        );

        $this->assertContains('email', $rules);
    }

    /** @test */
    public function it_converts_url_validation(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::TEXT,
            ['url']
        );

        $this->assertContains('url', $rules);
    }

    /** @test */
    public function it_converts_regex_validation_with_parameter(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::TEXT,
            [['type' => 'regex', 'parameter' => '/^[a-z]+$/']]
        );

        $this->assertContains('regex:/^[a-z]+$/', $rules);
    }

    /** @test */
    public function it_converts_min_validation_for_numbers(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::NUMBER,
            [['type' => 'min', 'parameter' => 10]]
        );

        $this->assertContains('min:10', $rules);
    }

    /** @test */
    public function it_converts_max_validation_for_numbers(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::NUMBER,
            [['type' => 'max', 'parameter' => 100]]
        );

        $this->assertContains('max:100', $rules);
    }

    /** @test */
    public function it_converts_integer_validation(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::NUMBER,
            ['integer']
        );

        $this->assertContains('integer', $rules);
    }

    /** @test */
    public function it_converts_decimal_validation(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::DECIMAL,
            ['decimal']
        );

        $this->assertContains('numeric', $rules);
    }

    /** @test */
    public function it_converts_image_validation(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::FILE,
            ['image']
        );

        $this->assertContains('image', $rules);
    }

    /** @test */
    public function it_converts_max_file_size_validation(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::FILE,
            [['type' => 'max_file_size', 'parameter' => 1024]] // 1024 KB
        );

        $this->assertContains('max:1048576', $rules); // 1024 * 1024 bytes
    }

    /** @test */
    public function it_converts_date_format_validation(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::DATE,
            [['type' => 'date_format', 'parameter' => 'Y-m-d']]
        );

        $this->assertContains('date_format:Y-m-d', $rules);
    }

    /** @test */
    public function it_adds_type_specific_default_rules(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::NUMBER,
            []
        );

        $this->assertContains('numeric', $rules);
        $this->assertContains('integer', $rules);
    }

    /** @test */
    public function it_validates_text_with_required_rule(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validate(
            AttributeType::TEXT,
            'test value',
            ['required']
        );
    }

    /** @test */
    public function it_throws_exception_when_required_value_is_missing(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            AttributeType::TEXT,
            '',
            ['required']
        );
    }

    /** @test */
    public function it_validates_min_length(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validate(
            AttributeType::TEXT,
            'test123',
            [['type' => 'min_length', 'parameter' => 5]]
        );
    }

    /** @test */
    public function it_throws_exception_when_min_length_not_met(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            AttributeType::TEXT,
            'test',
            [['type' => 'min_length', 'parameter' => 10]]
        );
    }

    /** @test */
    public function it_validates_max_length(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validate(
            AttributeType::TEXT,
            'test',
            [['type' => 'max_length', 'parameter' => 10]]
        );
    }

    /** @test */
    public function it_throws_exception_when_max_length_exceeded(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            AttributeType::TEXT,
            'this is too long',
            [['type' => 'max_length', 'parameter' => 5]]
        );
    }

    /** @test */
    public function it_validates_email(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validate(
            AttributeType::TEXT,
            'test@example.com',
            ['email']
        );
    }

    /** @test */
    public function it_throws_exception_for_invalid_email(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            AttributeType::TEXT,
            'invalid-email',
            ['email']
        );
    }

    /** @test */
    public function it_validates_number_with_min_max(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validate(
            AttributeType::NUMBER,
            50,
            [
                ['type' => 'min', 'parameter' => 10],
                ['type' => 'max', 'parameter' => 100],
            ]
        );
    }

    /** @test */
    public function it_throws_exception_when_number_below_min(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            AttributeType::NUMBER,
            5,
            [['type' => 'min', 'parameter' => 10]]
        );
    }

    /** @test */
    public function it_throws_exception_when_number_above_max(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(
            AttributeType::NUMBER,
            150,
            [['type' => 'max', 'parameter' => 100]]
        );
    }

    /** @test */
    public function it_handles_multiple_validations(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::TEXT,
            [
                'required',
                ['type' => 'min_length', 'parameter' => 5],
                ['type' => 'max_length', 'parameter' => 100],
                'email',
            ]
        );

        $this->assertContains('required', $rules);
        $this->assertContains('min:5', $rules);
        $this->assertContains('max:100', $rules);
        $this->assertContains('email', $rules);
    }

    /** @test */
    public function it_handles_string_validation_format(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::TEXT,
            ['required', 'email']
        );

        $this->assertContains('required', $rules);
        $this->assertContains('email', $rules);
    }

    /** @test */
    public function it_handles_array_validation_format(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::TEXT,
            [
                ['type' => 'required'],
                ['type' => 'min_length', 'parameter' => 5],
            ]
        );

        $this->assertContains('required', $rules);
        $this->assertContains('min:5', $rules);
    }

    /** @test */
    public function it_returns_available_validations_for_attribute_type(): void
    {
        $validations = $this->validator->getAvailableValidations(AttributeType::TEXT);

        $this->assertIsArray($validations);
        $this->assertNotEmpty($validations);
        $this->assertContains(ValidationType::REQUIRED, $validations);
        $this->assertContains(ValidationType::MIN_LENGTH, $validations);
        $this->assertContains(ValidationType::MAX_LENGTH, $validations);
    }

    /** @test */
    public function it_handles_invalid_validation_type_gracefully(): void
    {
        $rules = $this->validator->getValidationRules(
            AttributeType::TEXT,
            ['invalid_validation_type']
        );

        // Should not throw exception, just ignore invalid types
        $this->assertIsArray($rules);
    }
}

