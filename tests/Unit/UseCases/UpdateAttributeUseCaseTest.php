<?php

namespace Fiachehr\LaravelEav\Tests\Unit\UseCases;

use Fiachehr\LaravelEav\Domain\Entities\Attribute;
use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Repositories\EloquentAttributeRepository;
use Fiachehr\LaravelEav\Tests\TestCase;
use Fiachehr\LaravelEav\Application\UseCases\UpdateAttributeUseCase;
use Illuminate\Support\Str;

class UpdateAttributeUseCaseTest extends TestCase
{
    protected UpdateAttributeUseCase $useCase;
    protected EloquentAttributeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentAttributeRepository();
        $this->useCase = new UpdateAttributeUseCase($this->repository);
    }

    /** @test */
    public function it_can_update_an_attribute(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Original Title',
            'slug' => 'original-slug',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $entity = $this->repository->findById($attribute->id);
        $updatedEntity = new Attribute(
            id: $entity->id,
            logicalId: $entity->logicalId,
            title: 'Updated Title',
            slug: 'updated-slug',
            type: AttributeType::NUMBER,
            description: 'Updated Description',
            values: ['option1', 'option2'],
            validations: ['required'],
            isActive: false,
            language: 'fa',
        );

        $updated = $this->useCase->execute($updatedEntity);

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertEquals('updated-slug', $updated->slug);
        $this->assertEquals(AttributeType::NUMBER, $updated->type);
        $this->assertFalse($updated->isActive);
    }

    /** @test */
    public function it_can_update_attribute_with_translations(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Original Title',
            'slug' => 'original-slug',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $entity = $this->repository->findById($attribute->id);
        $translations = [
            'title' => [
                'en' => 'Updated Title',
                'fa' => 'عنوان به‌روز شده',
            ],
        ];

        $translationHandler = function ($model, $translations) {
            foreach ($translations as $key => $localeValues) {
                foreach ($localeValues as $locale => $value) {
                    $model->setTranslation($key, $locale, $value);
                }
            }
        };

        $updated = $this->useCase->execute($entity, $translations, $translationHandler);

        $this->assertNotNull($updated->id);
        
        $eloquentAttribute = EloquentAttribute::find($updated->id);
        $this->assertEquals('Updated Title', $eloquentAttribute->getTranslation('title', 'en'));
        $this->assertEquals('عنوان به‌روز شده', $eloquentAttribute->getTranslation('title', 'fa'));
    }
}

