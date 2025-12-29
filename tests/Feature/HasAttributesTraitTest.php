<?php

namespace Fiachehr\LaravelEav\Tests\Feature;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Shared\Traits\HasAttributes;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeGroup;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeValue;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HasAttributesTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_set_and_get_attribute_values(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product->setEavAttributeValue('color', 'red');

        $value = $product->getEavAttributeValue('color');
        $this->assertEquals('red', $value);
    }

    /** @test */
    public function it_can_set_multiple_attribute_values(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $colorAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $sizeAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Size',
            'slug' => 'size',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product->setEavAttributeValues([
            'color' => 'red',
            'size' => 'large',
        ]);

        $this->assertEquals('red', $product->getEavAttributeValue('color'));
        $this->assertEquals('large', $product->getEavAttributeValue('size'));
    }

    /** @test */
    public function it_can_get_all_attribute_values(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $colorAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $sizeAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Size',
            'slug' => 'size',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product->setEavAttributeValues([
            'color' => 'red',
            'size' => 'large',
        ]);

        $values = $product->getEavAttributeValues('slug');

        $this->assertEquals('red', $values['color']);
        $this->assertEquals('large', $values['size']);
    }

    /** @test */
    public function it_can_sync_attribute_values(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $colorAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $sizeAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Size',
            'slug' => 'size',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product->setEavAttributeValue('color', 'red');
        $product->setEavAttributeValue('size', 'large');

        $product->syncEavAttributeValues([
            'color' => 'blue',
        ]);

        $this->assertEquals('blue', $product->getEavAttributeValue('color'));
        $this->assertNull($product->getEavAttributeValue('size'));
    }

    /** @test */
    public function it_can_remove_attribute_value(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product->setEavAttributeValue('color', 'red');
        $this->assertEquals('red', $product->getEavAttributeValue('color'));

        $product->removeEavAttributeValue('color');
        $this->assertNull($product->getEavAttributeValue('color'));
    }

    /** @test */
    public function it_can_clear_all_attribute_values(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $colorAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $sizeAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Size',
            'slug' => 'size',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product->setEavAttributeValues([
            'color' => 'red',
            'size' => 'large',
        ]);

        $product->clearEavAttributeValues();

        $this->assertNull($product->getEavAttributeValue('color'));
        $this->assertNull($product->getEavAttributeValue('size'));
    }

    /** @test */
    public function it_can_search_by_attribute_value(): void
    {
        $colorAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('color', 'red');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('color', 'blue');

        $results = TestProduct::whereEavAttribute('color', 'red')->get();

        $this->assertCount(1, $results);
        $this->assertEquals(1, $results->first()->id);
    }

    /** @test */
    public function it_can_search_by_attribute_value_like(): void
    {
        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('title', 'Laptop Computer');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('title', 'Desktop Computer');

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('title', 'Mobile Phone');

        $results = TestProduct::whereEavAttributeLike('title', 'Computer')->get();

        $this->assertCount(2, $results);
    }

    /** @test */
    public function it_can_search_by_number_range(): void
    {
        $priceAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Price',
            'slug' => 'price',
            'type' => AttributeType::NUMBER->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('price', 50);

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('price', 150);

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('price', 200);

        $results = TestProduct::whereEavAttributeBetween('price', 100, 200)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', 2));
        $this->assertTrue($results->contains('id', 3));
    }

    /** @test */
    public function it_can_search_by_date_range(): void
    {
        $dateAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Created Date',
            'slug' => 'created-date',
            'type' => AttributeType::DATE->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('created-date', '2024-01-15');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('created-date', '2024-06-20');

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('created-date', '2024-12-25');

        $results = TestProduct::whereEavAttributeDateBetween('created-date', '2024-01-01', '2024-06-30')->get();

        $this->assertCount(2, $results);
    }

    /** @test */
    public function it_can_search_by_multiple_attributes(): void
    {
        $colorAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $sizeAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Size',
            'slug' => 'size',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('color', 'red');
        $product1->setEavAttributeValue('size', 'large');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('color', 'red');
        $product2->setEavAttributeValue('size', 'small');

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('color', 'blue');
        $product3->setEavAttributeValue('size', 'large');

        $results = TestProduct::whereEavAttributes([
            ['attribute' => 'color', 'value' => 'red'],
            ['attribute' => 'size', 'value' => 'large'],
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals(1, $results->first()->id);
    }

    /** @test */
    public function it_can_work_with_attribute_groups(): void
    {
        $group = EloquentAttributeGroup::create([
            'title' => 'Product Info',
            'slug' => 'product-info',
            'is_active' => true,
            'language' => 'en',
        ]);

        $colorAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $sizeAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Size',
            'slug' => 'size',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $group->attributes()->attach([$colorAttr->id, $sizeAttr->id]);

        $product = new TestProduct();
        $product->id = 1;
        $product->save();
        $product->syncEavAttributeGroups([$group->id]);

        $this->assertCount(1, $product->eavAttributeGroups);
        $this->assertEquals($group->id, $product->eavAttributeGroups->first()->id);
    }

    /** @test */
    public function it_handles_different_attribute_types(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        // Text
        $textAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Text Field',
            'slug' => 'text-field',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);
        $product->setEavAttributeValue('text-field', 'test text');
        $this->assertEquals('test text', $product->getEavAttributeValue('text-field'));

        // Number
        $numberAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Number Field',
            'slug' => 'number-field',
            'type' => AttributeType::NUMBER->value,
            'is_active' => true,
            'language' => 'en',
        ]);
        $product->setEavAttributeValue('number-field', 100);
        $this->assertEquals(100, $product->getEavAttributeValue('number-field'));

        // Decimal
        $decimalAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Decimal Field',
            'slug' => 'decimal-field',
            'type' => AttributeType::DECIMAL->value,
            'is_active' => true,
            'language' => 'en',
        ]);
        $product->setEavAttributeValue('decimal-field', 99.99);
        $this->assertEquals(99.99, $product->getEavAttributeValue('decimal-field'));

        // Boolean
        $booleanAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Boolean Field',
            'slug' => 'boolean-field',
            'type' => AttributeType::BOOLEAN->value,
            'is_active' => true,
            'language' => 'en',
        ]);
        $product->setEavAttributeValue('boolean-field', true);
        $this->assertTrue($product->getEavAttributeValue('boolean-field'));

        // Date
        $dateAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Date Field',
            'slug' => 'date-field',
            'type' => AttributeType::DATE->value,
            'is_active' => true,
            'language' => 'en',
        ]);
        $product->setEavAttributeValue('date-field', '2024-01-01');
        $dateValue = $product->getEavAttributeValue('date-field');
        // DATE type returns Carbon object, so we need to format it
        $this->assertEquals('2024-01-01', $dateValue instanceof \Carbon\Carbon ? $dateValue->format('Y-m-d') : $dateValue);
    }
}

