<?php

namespace Fiachehr\LaravelEav\Tests\Unit\Repositories;

use Fiachehr\LaravelEav\Domain\Entities\AttributeGroup;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeGroup;
use Fiachehr\LaravelEav\Infrastructure\Repositories\EloquentAttributeGroupRepository;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Support\Str;

class EloquentAttributeGroupRepositoryTest extends TestCase
{
    protected EloquentAttributeGroupRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentAttributeGroupRepository();
    }

    /** @test */
    public function it_can_create_an_attribute_group(): void
    {
        $group = new AttributeGroup(
            id: null,
            title: 'Test Group',
            slug: 'test-group',
            isActive: true,
            language: 'en',
            moduleId: null,
            attributeIds: [],
        );

        $created = $this->repository->create($group);

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

        $group = new AttributeGroup(
            id: null,
            title: 'Group With Attributes',
            slug: 'group-with-attributes',
            isActive: true,
            language: 'en',
            moduleId: null,
            attributeIds: [$attr1->id, $attr2->id],
        );

        $created = $this->repository->create($group);

        $this->assertCount(2, $created->attributeIds);
        $this->assertContains($attr1->id, $created->attributeIds);
        $this->assertContains($attr2->id, $created->attributeIds);
    }

    /** @test */
    public function it_can_find_group_by_id(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'Find Me',
            'slug' => 'find-me',
            'is_active' => true,
            'language' => 'en',
        ]);

        $found = $this->repository->findById($group->id);

        $this->assertNotNull($found);
        $this->assertEquals('Find Me', $found->title);
    }

    /** @test */
    public function it_can_find_group_by_slug(): void
    {
        EloquentAttributeGroup::create([
            'title' => 'Find By Slug',
            'slug' => 'find-by-slug',
            'is_active' => true,
            'language' => 'en',
        ]);

        $found = $this->repository->findBySlug('find-by-slug');

        $this->assertNotNull($found);
        $this->assertEquals('Find By Slug', $found->title);
    }

    /** @test */
    public function it_can_find_all_groups(): void
    {
        EloquentAttributeGroup::create([
            'title' => 'Group 1',
            'slug' => 'group-1',
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttributeGroup::create([
            'title' => 'Group 2',
            'slug' => 'group-2',
            'is_active' => true,
            'language' => 'en',
        ]);

        $all = $this->repository->findAll();

        $this->assertCount(2, $all);
    }

    /** @test */
    public function it_can_find_only_active_groups(): void
    {
        EloquentAttributeGroup::create([
            'title' => 'Active Group',
            'slug' => 'active-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttributeGroup::create([
            'title' => 'Inactive Group',
            'slug' => 'inactive-group',
            'is_active' => false,
            'language' => 'en',
        ]);

        $active = $this->repository->findActive();

        $this->assertCount(1, $active);
        $this->assertEquals('Active Group', $active->first()->title);
    }

    /** @test */
    public function it_can_find_groups_by_language(): void
    {
        EloquentAttributeGroup::create([
            'title' => 'English Group',
            'slug' => 'english-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttributeGroup::create([
            'title' => 'Persian Group',
            'slug' => 'persian-group',
            'is_active' => true,
            'language' => 'fa',
        ]);

        $english = $this->repository->findByLanguage('en');
        $persian = $this->repository->findByLanguage('fa');

        $this->assertCount(1, $english);
        $this->assertEquals('English Group', $english->first()->title);
        $this->assertCount(1, $persian);
        $this->assertEquals('Persian Group', $persian->first()->title);
    }

    /** @test */
    public function it_can_find_groups_by_current_language(): void
    {
        app()->setLocale('en');

        EloquentAttributeGroup::create([
            'title' => 'Current Language Group',
            'slug' => 'current-language-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttributeGroup::create([
            'title' => 'Other Language Group',
            'slug' => 'other-language-group',
            'is_active' => true,
            'language' => 'fa',
        ]);

        $current = $this->repository->findByCurrentLanguage();

        $this->assertCount(1, $current);
        $this->assertEquals('Current Language Group', $current->first()->title);
    }

    /** @test */
    public function it_can_find_groups_by_multiple_languages(): void
    {
        EloquentAttributeGroup::create([
            'title' => 'English Group',
            'slug' => 'english-group',
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttributeGroup::create([
            'title' => 'Persian Group',
            'slug' => 'persian-group',
            'is_active' => true,
            'language' => 'fa',
        ]);

        EloquentAttributeGroup::create([
            'title' => 'German Group',
            'slug' => 'german-group',
            'is_active' => true,
            'language' => 'de',
        ]);

        $multi = $this->repository->findByLanguages(['en', 'fa']);

        $this->assertCount(2, $multi);
        $this->assertTrue($multi->contains('title', 'English Group'));
        $this->assertTrue($multi->contains('title', 'Persian Group'));
        $this->assertFalse($multi->contains('title', 'German Group'));
    }

    /** @test */
    public function it_can_update_a_group(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'Original Title',
            'slug' => 'original-slug',
            'is_active' => true,
            'language' => 'en',
        ]);

        $entity = $this->repository->findById($group->id);
        $updatedEntity = new AttributeGroup(
            id: $entity->id,
            title: 'Updated Title',
            slug: 'updated-slug',
            isActive: false,
            language: 'fa',
            moduleId: 1,
            attributeIds: [],
        );

        $updated = $this->repository->update($updatedEntity);

        $this->assertEquals('Updated Title', $updated->title);
        $this->assertEquals('updated-slug', $updated->slug);
        $this->assertFalse($updated->isActive);
        $this->assertEquals('fa', $updated->language);
        $this->assertEquals(1, $updated->moduleId);
    }

    /** @test */
    public function it_can_update_group_with_attributes(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'Test Group',
            'slug' => 'test-group',
            'is_active' => true,
            'language' => 'en',
        ]);

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

        $entity = $this->repository->findById($group->id);
        $updatedEntity = new AttributeGroup(
            id: $entity->id,
            title: $entity->title,
            slug: $entity->slug,
            isActive: $entity->isActive,
            language: $entity->language,
            moduleId: $entity->moduleId,
            attributeIds: [$attr1->id, $attr2->id],
        );

        $updated = $this->repository->update($updatedEntity);

        $this->assertCount(2, $updated->attributeIds);
    }

    /** @test */
    public function it_can_delete_a_group(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'To Delete',
            'slug' => 'to-delete',
            'is_active' => true,
            'language' => 'en',
        ]);

        $deleted = $this->repository->delete($group->id);

        $this->assertTrue($deleted);
        $this->assertNull($this->repository->findById($group->id));
    }

    /** @test */
    public function it_can_sync_attributes_for_group(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'Test Group',
            'slug' => 'test-group',
            'is_active' => true,
            'language' => 'en',
        ]);

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

        $this->repository->syncAttributes($group->id, [$attr1->id, $attr2->id]);

        $group = $this->repository->findById($group->id);
        $this->assertCount(2, $group->attributeIds);
    }
}

