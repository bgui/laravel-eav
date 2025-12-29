<?php

namespace Fiachehr\LaravelEav\Tests\Feature;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Shared\Traits\HasAttributes;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Query\EavQueryBuilder;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EavQueryBuilderTest extends TestCase
{
    /** @test */
    public function it_can_filter_by_text_value(): void
    {
        $attribute = EloquentAttribute::create([
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
        $product1->setEavAttributeValue('title', 'Product 1');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('title', 'Product 2');

        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereText('title', 'Product 1')
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(1, $ids->first());
    }

    /** @test */
    public function it_can_filter_by_text_like(): void
    {
        $attribute = EloquentAttribute::create([
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

        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereTextLike('title', 'Computer')
            ->getAttributableIds();

        $this->assertCount(2, $ids);
    }

    /** @test */
    public function it_can_filter_by_number_value(): void
    {
        $attribute = EloquentAttribute::create([
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
        $product1->setEavAttributeValue('price', 100);

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('price', 200);

        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereNumber('price', 100)
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(1, $ids->first());
    }

    /** @test */
    public function it_can_filter_by_number_range(): void
    {
        $attribute = EloquentAttribute::create([
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
        $product3->setEavAttributeValue('price', 250);

        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereNumberBetween('price', 100, 200)
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(2, $ids->first());
    }

    /** @test */
    public function it_can_filter_by_boolean_value(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Is Active',
            'slug' => 'is-active',
            'type' => AttributeType::BOOLEAN->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('is-active', true);

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('is-active', false);

        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereBoolean('is-active', true)
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(1, $ids->first());
    }

    /** @test */
    public function it_can_filter_by_date_range(): void
    {
        $attribute = EloquentAttribute::create([
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

        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereDateBetween('created-date', '2024-01-01', '2024-03-31')
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(1, $ids->first());
    }

    /** @test */
    public function it_can_filter_by_multiple_conditions(): void
    {
        $colorAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

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
        $product1->setEavAttributeValue('color', 'red');
        $product1->setEavAttributeValue('price', 100);

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('color', 'red');
        $product2->setEavAttributeValue('price', 200);

        $ids = (new EavQueryBuilder(TestProduct::class))
            ->whereMultiple([
                ['attribute' => 'color', 'value' => 'red', 'type' => 'text'],
                ['attribute' => 'price', 'value' => 100, 'type' => 'number'],
            ])
            ->getAttributableIds();

        $this->assertCount(1, $ids);
        $this->assertEquals(1, $ids->first());
    }

    /** @test */
    public function it_can_get_count(): void
    {
        $attribute = EloquentAttribute::create([
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
        $product1->setEavAttributeValue('title', 'Product 1');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('title', 'Product 2');

        $count = (new EavQueryBuilder(TestProduct::class))
            ->whereTextLike('title', 'Product')
            ->count();

        $this->assertEquals(2, $count);
    }

    /** @test */
    public function it_can_get_sum_of_numeric_values(): void
    {
        $attribute = EloquentAttribute::create([
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
        $product1->setEavAttributeValue('price', 100);

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('price', 200);

        $sum = (new EavQueryBuilder(TestProduct::class))
            ->sum('price');

        $this->assertEquals(300, $sum);
    }

    /** @test */
    public function it_can_get_average_of_numeric_values(): void
    {
        $attribute = EloquentAttribute::create([
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
        $product1->setEavAttributeValue('price', 100);

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('price', 200);

        $avg = (new EavQueryBuilder(TestProduct::class))
            ->avg('price');

        $this->assertEquals(150, $avg);
    }
}
