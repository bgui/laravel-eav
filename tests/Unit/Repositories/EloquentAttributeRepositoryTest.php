<?php

namespace Fiachehr\LaravelEav\Tests\Unit\Repositories;

use Fiachehr\LaravelEav\Domain\Entities\Attribute;
use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeGroup;
use Fiachehr\LaravelEav\Infrastructure\Repositories\EloquentAttributeRepository;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Support\Str;

class EloquentAttributeRepositoryTest extends TestCase
{
    protected EloquentAttributeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentAttributeRepository();
    }

    /** @test */
    public function it_can_create_an_attribute(): void
    {
        $attribute = new Attribute(
            id: null,
            logicalId: Str::uuid()->toString(),
            title: 'Test Attribute',
            slug: 'test-attribute',
            type: AttributeType::TEXT,
            description: 'Test Description',
            values: [],
            validations: [],
            isActive: true,
            language: 'en',
        );

        $created = $this->repository->create($attribute);

        $this->assertNotNull($created->id);
        $this->assertEquals('Test Attribute', $created->title);
        $this->assertEquals('test-attribute', $created->slug);
        $this->assertEquals(AttributeType::TEXT, $created->type);
        $this->assertTrue($created->isActive);
    }

    /** @test */
    public function it_can_find_an_attribute_by_id(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Find Me',
            'slug' => 'find-me',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $found = $this->repository->findById($attribute->id);

        $this->assertNotNull($found);
        $this->assertEquals('Find Me', $found->title);
        $this->assertEquals($attribute->id, $found->id);
    }

    /** @test */
    public function it_returns_null_when_attribute_not_found_by_id(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    /** @test */
    public function it_can_find_an_attribute_by_slug(): void
    {
        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Find By Slug',
            'slug' => 'find-by-slug',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $found = $this->repository->findBySlug('find-by-slug');

        $this->assertNotNull($found);
        $this->assertEquals('Find By Slug', $found->title);
    }

    /** @test */
    public function it_can_find_an_attribute_by_logical_id(): void
    {
        $logicalId = Str::uuid()->toString();
        EloquentAttribute::create([
            'logical_id' => $logicalId,
            'title' => 'Find By Logical ID',
            'slug' => 'find-by-logical-id',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $found = $this->repository->findByLogicalId($logicalId);

        $this->assertNotNull($found);
        $this->assertEquals($logicalId, $found->logicalId);
    }

    /** @test */
    public function it_can_find_all_attributes(): void
    {
        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 1',
            'slug' => 'attribute-1',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 2',
            'slug' => 'attribute-2',
            'type' => AttributeType::NUMBER->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $all = $this->repository->findAll();

        $this->assertCount(2, $all);
    }

    /** @test */
    public function it_can_find_only_active_attributes(): void
    {
        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Active Attribute',
            'slug' => 'active-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Inactive Attribute',
            'slug' => 'inactive-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => false,
            'language' => 'en',
        ]);

        $active = $this->repository->findActive();

        $this->assertCount(1, $active);
        $this->assertEquals('Active Attribute', $active->first()->title);
    }

    /** @test */
    public function it_can_find_attributes_by_language(): void
    {
        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Attribute',
            'slug' => 'english-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Persian Attribute',
            'slug' => 'persian-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'fa',
        ]);

        $english = $this->repository->findByLanguage('en');
        $persian = $this->repository->findByLanguage('fa');

        $this->assertCount(1, $english);
        $this->assertEquals('English Attribute', $english->first()->title);
        $this->assertCount(1, $persian);
        $this->assertEquals('Persian Attribute', $persian->first()->title);
    }

    /** @test */
    public function it_can_find_attributes_by_current_language(): void
    {
        app()->setLocale('en');

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Current Language Attribute',
            'slug' => 'current-language-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Other Language Attribute',
            'slug' => 'other-language-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'fa',
        ]);

        $current = $this->repository->findByCurrentLanguage();

        $this->assertCount(1, $current);
        $this->assertEquals('Current Language Attribute', $current->first()->title);
    }

    /** @test */
    public function it_can_find_attributes_by_multiple_languages(): void
    {
        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Attribute',
            'slug' => 'english-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Persian Attribute',
            'slug' => 'persian-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'fa',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'German Attribute',
            'slug' => 'german-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'de',
        ]);

        $multi = $this->repository->findByLanguages(['en', 'fa']);

        $this->assertCount(2, $multi);
        $this->assertTrue($multi->contains('title', 'English Attribute'));
        $this->assertTrue($multi->contains('title', 'Persian Attribute'));
        $this->assertFalse($multi->contains('title', 'German Attribute'));
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

        $updated = $this->repository->update($updatedEntity);

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertEquals('updated-slug', $updated->slug);
        $this->assertEquals(AttributeType::NUMBER, $updated->type);
        $this->assertEquals('Updated Description', $updated->description);
        $this->assertFalse($updated->isActive);
        $this->assertEquals('fa', $updated->language);
    }

    /** @test */
    public function it_can_delete_an_attribute(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'To Delete',
            'slug' => 'to-delete',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $deleted = $this->repository->delete($attribute->id);

        $this->assertTrue($deleted);
        $this->assertNull($this->repository->findById($attribute->id));
    }

    /** @test */
    public function it_can_attach_attribute_to_group(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Test Attribute',
            'slug' => 'test-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $group = EloquentAttributeGroup::create([
            'title' => 'Test Group',
            'slug' => 'test-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        $this->repository->attachToGroup($attribute->id, $group->id);

        $this->assertTrue($attribute->fresh()->groups->contains($group->id));
    }

    /** @test */
    public function it_can_detach_attribute_from_group(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Test Attribute',
            'slug' => 'test-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $group = EloquentAttributeGroup::create([
            'title' => 'Test Group',
            'slug' => 'test-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->groups()->attach($group->id);
        $this->assertTrue($attribute->fresh()->groups->contains($group->id));

        $this->repository->detachFromGroup($attribute->id, $group->id);

        $this->assertFalse($attribute->fresh()->groups->contains($group->id));
    }

    /** @test */
    public function it_can_find_attributes_by_group_ids(): void
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
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr2 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 2',
            'slug' => 'attribute-2',
            'type' => AttributeType::NUMBER->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr3 = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 3',
            'slug' => 'attribute-3',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attr1->groups()->attach($group1->id);
        $attr2->groups()->attach($group1->id);
        $attr3->groups()->attach($group2->id);

        $found = $this->repository->findByGroupIds([$group1->id]);

        $this->assertCount(2, $found);
        $this->assertTrue($found->contains('slug', 'attribute-1'));
        $this->assertTrue($found->contains('slug', 'attribute-2'));
        $this->assertFalse($found->contains('slug', 'attribute-3'));
    }

    /** @test */
    public function it_can_find_attributes_by_slugs(): void
    {
        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 1',
            'slug' => 'attribute-1',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 2',
            'slug' => 'attribute-2',
            'type' => AttributeType::NUMBER->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Attribute 3',
            'slug' => 'attribute-3',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $found = $this->repository->findBySlugs(['attribute-1', 'attribute-2']);

        $this->assertCount(2, $found);
        $this->assertTrue($found->contains('slug', 'attribute-1'));
        $this->assertTrue($found->contains('slug', 'attribute-2'));
        $this->assertFalse($found->contains('slug', 'attribute-3'));
    }

    /** @test */
    public function it_returns_empty_collection_when_finding_by_empty_group_ids(): void
    {
        $found = $this->repository->findByGroupIds([]);

        $this->assertCount(0, $found);
    }

    /** @test */
    public function it_returns_empty_collection_when_finding_by_empty_slugs(): void
    {
        $found = $this->repository->findBySlugs([]);

        $this->assertCount(0, $found);
    }

    /** @test */
    public function it_handles_all_attribute_types(): void
    {
        $types = AttributeType::cases();

        foreach ($types as $type) {
            $attribute = new Attribute(
                id: null,
                logicalId: Str::uuid()->toString(),
                title: "Test {$type->label()}",
                slug: "test-{$type->value}",
                type: $type,
                description: "Test description for {$type->label()}",
                values: $type->requiresValues() ? ['value1', 'value2'] : [],
                validations: [],
                isActive: true,
                language: 'en',
            );

            $created = $this->repository->create($attribute);

            $this->assertEquals($type, $created->type);
            $this->assertNotNull($created->id);
        }
    }
}

