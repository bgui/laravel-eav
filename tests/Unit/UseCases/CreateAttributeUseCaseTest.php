<?php

namespace Fiachehr\LaravelEav\Tests\Unit\UseCases;

use Fiachehr\LaravelEav\Application\DTOs\CreateAttributeDTO;
use Fiachehr\LaravelEav\Application\UseCases\CreateAttributeUseCase;
use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Repositories\EloquentAttributeRepository;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Support\Str;

class CreateAttributeUseCaseTest extends TestCase
{
    protected CreateAttributeUseCase $useCase;
    protected EloquentAttributeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentAttributeRepository();
        $this->useCase = new CreateAttributeUseCase($this->repository);
    }

    /** @test */
    public function it_can_create_an_attribute(): void
    {
        $dto = CreateAttributeDTO::fromArray([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Test Attribute',
            'slug' => 'test-attribute',
            'type' => AttributeType::TEXT->value,
            'description' => 'Test Description',
            'values' => [],
            'validations' => [],
            'is_active' => true,
            'language' => 'en',
        ]);

        $created = $this->useCase->execute($dto);

        $this->assertNotNull($created->id);
        $this->assertEquals('Test Attribute', $created->title);
        $this->assertEquals('test-attribute', $created->slug);
        $this->assertEquals(AttributeType::TEXT, $created->type);
    }

    /** @test */
    public function it_can_create_attribute_with_translations(): void
    {
        $dto = CreateAttributeDTO::fromArray([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Test Attribute',
            'slug' => 'test-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $translations = [
            'title' => [
                'en' => 'Test Attribute',
                'fa' => 'ویژگی تست',
                'de' => 'Test-Attribut',
            ],
            'description' => [
                'en' => 'Test Description',
                'fa' => 'توضیحات تست',
            ],
        ];

        $translationHandler = function ($model, $translations) {
            foreach ($translations as $key => $localeValues) {
                foreach ($localeValues as $locale => $value) {
                    $model->setTranslation($key, $locale, $value);
                }
            }
        };

        $created = $this->useCase->execute($dto, $translations, $translationHandler);

        $this->assertNotNull($created->id);
        
        $eloquentAttribute = EloquentAttribute::find($created->id);
        $this->assertEquals('Test Attribute', $eloquentAttribute->getTranslation('title', 'en'));
        $this->assertEquals('ویژگی تست', $eloquentAttribute->getTranslation('title', 'fa'));
        $this->assertEquals('Test-Attribut', $eloquentAttribute->getTranslation('title', 'de'));
    }

    /** @test */
    public function it_can_create_attribute_with_values(): void
    {
        $dto = CreateAttributeDTO::fromArray([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Select Attribute',
            'slug' => 'select-attribute',
            'type' => AttributeType::SELECT->value,
            'values' => ['option1', 'option2', 'option3'],
            'validations' => [],
            'is_active' => true,
            'language' => 'en',
        ]);

        $created = $this->useCase->execute($dto);

        $this->assertNotNull($created->id);
        $this->assertCount(3, $created->values);
        $this->assertContains('option1', $created->values);
    }

    /** @test */
    public function it_can_create_attribute_with_validations(): void
    {
        $dto = CreateAttributeDTO::fromArray([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Validated Attribute',
            'slug' => 'validated-attribute',
            'type' => AttributeType::TEXT->value,
            'validations' => ['required', 'email'],
            'is_active' => true,
            'language' => 'en',
        ]);

        $created = $this->useCase->execute($dto);

        $this->assertNotNull($created->id);
        $this->assertCount(2, $created->validations);
        $this->assertContains('required', $created->validations);
        $this->assertContains('email', $created->validations);
    }

    /** @test */
    public function it_handles_all_attribute_types(): void
    {
        $types = AttributeType::cases();

        foreach ($types as $type) {
            $dto = CreateAttributeDTO::fromArray([
                'logical_id' => Str::uuid()->toString(),
                'title' => "Test {$type->label()}",
                'slug' => "test-{$type->value}",
                'type' => $type->value,
                'is_active' => true,
                'language' => 'en',
            ]);

            $created = $this->useCase->execute($dto);

            $this->assertEquals($type, $created->type);
        }
    }
}

