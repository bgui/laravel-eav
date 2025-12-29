<?php

namespace Fiachehr\LaravelEav\Tests\Feature;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Shared\Traits\HasAttributes;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeValue;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MultilingualSearchTest extends TestCase
{
    /** @test */
    public function it_can_search_by_translated_attribute_title(): void
    {
        // Create attribute with translations
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Color',
            'slug' => 'color',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslation('title', 'fa', 'رنگ');
        $attribute->setTranslation('title', 'de', 'Farbe');

        // Create products with values
        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('color', 'red');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('color', 'blue');

        // Search should work regardless of translation
        $results = TestProduct::whereEavAttribute('color', 'red')->get();

        $this->assertCount(1, $results);
        $this->assertEquals(1, $results->first()->id);
    }

    /** @test */
    public function it_can_search_attributes_by_language(): void
    {
        $enAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Attribute',
            'slug' => 'english-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $faAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Persian Attribute',
            'slug' => 'persian-attribute',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'fa',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('english-attribute', 'value1');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('persian-attribute', 'value2');

        // Search using English attribute
        $results = TestProduct::whereEavAttribute('english-attribute', 'value1')->get();
        $this->assertCount(1, $results);

        // Search using Persian attribute
        $results = TestProduct::whereEavAttribute('persian-attribute', 'value2')->get();
        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_can_search_with_multilingual_attribute_values(): void
    {
        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        // Create products with different language values
        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('title', 'Laptop');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('title', 'لپتاپ'); // Persian

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('title', 'Laptop Computer');

        // Search for English value
        $results = TestProduct::whereEavAttributeLike('title', 'Laptop')->get();
        $this->assertCount(2, $results);

        // Search for Persian value
        $results = TestProduct::whereEavAttribute('title', 'لپتاپ')->get();
        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_can_search_across_multiple_languages(): void
    {
        $descriptionAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Description',
            'slug' => 'description',
            'type' => AttributeType::TEXTAREA->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('description', 'High quality product');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('description', 'محصول با کیفیت بالا'); // Persian

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('description', 'Hochwertiges Produkt'); // German

        // Search should find all products regardless of language
        $results = TestProduct::whereEavAttributeLike('description', 'quality')->get();
        $this->assertCount(1, $results); // Only English contains "quality"

        $results = TestProduct::whereEavAttributeLike('description', 'محصول')->get();
        $this->assertCount(1, $results); // Only Persian contains "محصول"
    }

    /** @test */
    public function it_can_search_with_translated_attribute_names(): void
    {
        // Create attribute with translations
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Price',
            'slug' => 'price',
            'type' => AttributeType::NUMBER->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslation('title', 'fa', 'قیمت');
        $attribute->setTranslation('title', 'de', 'Preis');

        // Slug remains the same regardless of language
        $product = new TestProduct();
        $product->id = 1;
        $product->save();
        $product->setEavAttributeValue('price', 100);

        // Search should work with slug (language-independent)
        $results = TestProduct::whereEavAttribute('price', 100)->get();
        $this->assertCount(1, $results);

        // Get attribute title in different languages
        // For default language (en), use the model's title field directly
        $this->assertEquals('Price', $attribute->title);
        $this->assertEquals('قیمت', $attribute->getTranslation('title', 'fa'));
        $this->assertEquals('Preis', $attribute->getTranslation('title', 'de'));
    }

    /** @test */
    public function it_can_filter_attributes_by_language_and_search(): void
    {
        $enAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'english-title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $faAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Persian Title',
            'slug' => 'persian-title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'fa',
        ]);

        $product1 = new TestProduct();
        $product1->id = 1;
        $product1->save();
        $product1->setEavAttributeValue('english-title', 'value1');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('persian-title', 'value2');

        // Get English attributes only
        $enAttributes = EloquentAttribute::where('language', 'en')->get();
        $this->assertCount(1, $enAttributes);
        $this->assertEquals('english-title', $enAttributes->first()->slug);

        // Search still works with slug
        $results = TestProduct::whereEavAttribute('english-title', 'value1')->get();
        $this->assertCount(1, $results);
    }
}
