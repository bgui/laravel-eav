<?php

namespace Fiachehr\LaravelEav\Tests\Unit\Services;

use Fiachehr\LaravelEav\Application\Services\EavValidationService;
use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Services\AttributeValidator;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeGroup;
use Fiachehr\LaravelEav\Infrastructure\Repositories\EloquentAttributeRepository;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Support\Str;

class EavValidationServiceTest extends TestCase
{
    protected EavValidationService $service;
    protected EloquentAttributeRepository $attributeRepository;
    protected AttributeValidator $attributeValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attributeRepository = new EloquentAttributeRepository();
        $this->attributeValidator = new AttributeValidator();
        $this->service = new EavValidationService(
            $this->attributeRepository,
            $this->attributeValidator
        );
    }

    /** @test */
    public function it_returns_validation_rules_for_attribute_groups(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'Test Group',
            'slug' => 'test-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr1 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Required Text',
            'slug' => 'required-text',
            'type' => AttributeType::TEXT->value,
            'validations' => ['required'],
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr2 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Email Field',
            'slug' => 'email-field',
            'type' => AttributeType::TEXT->value,
            'validations' => ['required', 'email'],
            'is_active' => true,
            'language' => 'en',
        ]);

        $group->attributes()->attach([$attr1->id, $attr2->id]);

        $rules = $this->service->getValidationRules([$group->id]);

        $this->assertArrayHasKey('eav_attributes.required-text', $rules);
        $this->assertArrayHasKey('eav_attributes.email-field', $rules);
        $this->assertContains('required', $rules['eav_attributes.required-text']);
        $this->assertContains('required', $rules['eav_attributes.email-field']);
        $this->assertContains('email', $rules['eav_attributes.email-field']);
    }

    /** @test */
    public function it_returns_empty_rules_for_empty_group_ids(): void
    {
        $rules = $this->service->getValidationRules([]);

        $this->assertEmpty($rules);
    }

    /** @test */
    public function it_returns_validation_rules_by_slugs(): void
    {
        $attr1 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Required Text',
            'slug' => 'required-text',
            'type' => AttributeType::TEXT->value,
            'validations' => ['required'],
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr2 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Number Field',
            'slug' => 'number-field',
            'type' => AttributeType::NUMBER->value,
            'validations' => ['required', ['type' => 'min', 'parameter' => 10]],
            'is_active' => true,
            'language' => 'en',
        ]);

        $rules = $this->service->getValidationRulesBySlugs(['required-text', 'number-field']);

        $this->assertArrayHasKey('eav_attributes.required-text', $rules);
        $this->assertArrayHasKey('eav_attributes.number-field', $rules);
        $this->assertContains('required', $rules['eav_attributes.required-text']);
        $this->assertContains('required', $rules['eav_attributes.number-field']);
        $this->assertContains('min:10', $rules['eav_attributes.number-field']);
    }

    /** @test */
    public function it_returns_empty_rules_for_empty_slugs(): void
    {
        $rules = $this->service->getValidationRulesBySlugs([]);

        $this->assertEmpty($rules);
    }

    /** @test */
    public function it_validates_eav_attributes_successfully(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'Test Group',
            'slug' => 'test-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr1 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Required Text',
            'slug' => 'required-text',
            'type' => AttributeType::TEXT->value,
            'validations' => ['required'],
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr2 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Email Field',
            'slug' => 'email-field',
            'type' => AttributeType::TEXT->value,
            'validations' => ['required', 'email'],
            'is_active' => true,
            'language' => 'en',
        ]);

        $group->attributes()->attach([$attr1->id, $attr2->id]);

        $validator = $this->service->validate(
            [
                'required-text' => 'test value',
                'email-field' => 'test@example.com',
            ],
            [$group->id]
        );

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_fails_validation_when_required_field_is_missing(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'Test Group',
            'slug' => 'test-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Required Text',
            'slug' => 'required-text',
            'type' => AttributeType::TEXT->value,
            'validations' => ['required'],
            'is_active' => true,
            'language' => 'en',
        ]);

        $group->attributes()->attach($attr->id);

        $validator = $this->service->validate(
            [],
            [$group->id]
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('eav_attributes.required-text'));
    }

    /** @test */
    public function it_fails_validation_when_email_is_invalid(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'Test Group',
            'slug' => 'test-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Email Field',
            'slug' => 'email-field',
            'type' => AttributeType::TEXT->value,
            'validations' => ['required', 'email'],
            'is_active' => true,
            'language' => 'en',
        ]);

        $group->attributes()->attach($attr->id);

        $validator = $this->service->validate(
            [
                'email-field' => 'invalid-email',
            ],
            [$group->id]
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('eav_attributes.email-field'));
    }

    /** @test */
    public function it_handles_attributes_without_validations(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'Test Group',
            'slug' => 'test-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'No Validation',
            'slug' => 'no-validation',
            'type' => AttributeType::TEXT->value,
            'validations' => [],
            'is_active' => true,
            'language' => 'en',
        ]);

        $group->attributes()->attach($attr->id);

        $rules = $this->service->getValidationRules([$group->id]);

        $this->assertArrayNotHasKey('eav_attributes.no-validation', $rules);
    }

    /** @test */
    public function it_handles_multiple_groups(): void
    {
        $group1 = EloquentAttributeGroup::create([
            'title' => 'Group 1',
            'slug' => 'group-1',
            'is_active' => true,
            'language' => 'en',
        ]);

        $group2 = EloquentAttributeGroup::create([
            'title' => 'Group 2',
            'slug' => 'group-2',
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr1 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 1',
            'slug' => 'attribute-1',
            'type' => AttributeType::TEXT->value,
            'validations' => ['required'],
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr2 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 2',
            'slug' => 'attribute-2',
            'type' => AttributeType::NUMBER->value,
            'validations' => ['required'],
            'is_active' => true,
            'language' => 'en',
        ]);

        $group1->attributes()->attach($attr1->id);
        $group2->attributes()->attach($attr2->id);

        $rules = $this->service->getValidationRules([$group1->id, $group2->id]);

        $this->assertArrayHasKey('eav_attributes.attribute-1', $rules);
        $this->assertArrayHasKey('eav_attributes.attribute-2', $rules);
    }
}

