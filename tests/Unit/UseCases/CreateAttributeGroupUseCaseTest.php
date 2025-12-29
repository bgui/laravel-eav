<?php

namespace Fiachehr\LaravelEav\Tests\Unit\UseCases;

use Fiachehr\LaravelEav\Application\DTOs\CreateAttributeGroupDTO;
use Fiachehr\LaravelEav\Application\UseCases\CreateAttributeGroupUseCase;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Repositories\EloquentAttributeGroupRepository;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Support\Str;

class CreateAttributeGroupUseCaseTest extends TestCase
{
    protected CreateAttributeGroupUseCase $useCase;
    protected EloquentAttributeGroupRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentAttributeGroupRepository();
        $this->useCase = new CreateAttributeGroupUseCase($this->repository);
    }

    /** @test */
    public function it_can_create_an_attribute_group(): void
    {
        $dto = CreateAttributeGroupDTO::fromArray([
            'title' => 'Test Group',
            'slug' => 'test-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        $created = $this->useCase->execute($dto);

        $this->assertNotNull($created->id);
        $this->assertEquals('Test Group', $created->title);
        $this->assertEquals('test-group', $created->slug);
        $this->assertTrue($created->isActive);
    }

    /** @test */
    public function it_can_create_group_with_attributes(): void
    {
        $attr1 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 1',
            'slug' => 'attribute-1',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr2 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 2',
            'slug' => 'attribute-2',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $dto = CreateAttributeGroupDTO::fromArray([
            'title' => 'Group With Attributes',
            'slug' => 'group-with-attributes',
            'is_active' => true,
            'language' => 'en',
            'attribute_ids' => [$attr1->id, $attr2->id],
        ]);

        $created = $this->useCase->execute($dto);

        $this->assertCount(2, $created->attributeIds);
        $this->assertContains($attr1->id, $created->attributeIds);
        $this->assertContains($attr2->id, $created->attributeIds);
    }

    /** @test */
    public function it_can_create_group_with_module_id(): void
    {
        $dto = CreateAttributeGroupDTO::fromArray([
            'title' => 'Group With Module',
            'slug' => 'group-with-module',
            'is_active' => true,
            'language' => 'en',
            'module_id' => 1,
        ]);

        $created = $this->useCase->execute($dto);

        $this->assertEquals(1, $created->moduleId);
    }
}

