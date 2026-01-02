<?php

namespace Fiachehr\LaravelEav\Tests\Feature;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttributeValue;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Support\Str;

class LocaleBasedAttributeValuesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    /** @test */
    public function it_can_set_and_get_attribute_values_for_different_locales(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        // Set values for different locales
        $product->setEavAttributeValue('title', 'English Title', 'en');
        $product->setEavAttributeValue('title', 'عنوان فارسی', 'fa');
        $product->setEavAttributeValue('title', 'Deutscher Titel', 'de');

        // Get values for each locale
        $this->assertEquals('English Title', $product->getEavAttributeValue('title', 'en'));
        $this->assertEquals('عنوان فارسی', $product->getEavAttributeValue('title', 'fa'));
        $this->assertEquals('Deutscher Titel', $product->getEavAttributeValue('title', 'de'));
    }

    /** @test */
    public function it_can_set_multiple_locale_values_at_once(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $descriptionAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Description',
            'slug' => 'description',
            'type' => AttributeType::TEXTAREA->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        // Set locale-based values using array format
        $product->setEavAttributeValues([
            'title' => [
                'en' => 'English Title',
                'fa' => 'عنوان فارسی',
                'de' => 'Deutscher Titel',
            ],
            'description' => [
                'en' => 'English Description',
                'fa' => 'توضیحات فارسی',
                'de' => 'Deutsche Beschreibung',
            ],
        ]);

        // Verify all locales
        $this->assertEquals('English Title', $product->getEavAttributeValue('title', 'en'));
        $this->assertEquals('عنوان فارسی', $product->getEavAttributeValue('title', 'fa'));
        $this->assertEquals('Deutscher Titel', $product->getEavAttributeValue('title', 'de'));

        $this->assertEquals('English Description', $product->getEavAttributeValue('description', 'en'));
        $this->assertEquals('توضیحات فارسی', $product->getEavAttributeValue('description', 'fa'));
        $this->assertEquals('Deutsche Beschreibung', $product->getEavAttributeValue('description', 'de'));
    }

    /** @test */
    public function it_can_get_all_attribute_values_grouped_by_locale(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product->setEavAttributeValue('title', 'English Title', 'en');
        $product->setEavAttributeValue('title', 'عنوان فارسی', 'fa');
        $product->setEavAttributeValue('title', 'Deutscher Titel', 'de');

        $values = $product->getEavAttributeValues('slug', null, true);

        $this->assertIsArray($values);
        $this->assertArrayHasKey('title', $values);
        $this->assertIsArray($values['title']);
        $this->assertEquals('English Title', $values['title']['en']);
        $this->assertEquals('عنوان فارسی', $values['title']['fa']);
        $this->assertEquals('Deutscher Titel', $values['title']['de']);
    }

    /** @test */
    public function it_uses_current_locale_when_locale_not_specified(): void
    {
        app()->setLocale('fa');

        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        // Set value without specifying locale
        $product->setEavAttributeValue('title', 'عنوان فارسی');

        // Should use current locale (fa)
        $this->assertEquals('عنوان فارسی', $product->getEavAttributeValue('title'));
        $this->assertEquals('عنوان فارسی', $product->getEavAttributeValue('title', 'fa'));
        $this->assertNull($product->getEavAttributeValue('title', 'en'));
    }

    /** @test */
    public function it_can_search_by_attribute_value_in_specific_locale(): void
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
        $product1->setEavAttributeValue('title', 'Laptop', 'en');
        $product1->setEavAttributeValue('title', 'لپ تاپ', 'fa');
        $product1->setEavAttributeValue('title', 'Laptop', 'de');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('title', 'Desktop', 'en');
        $product2->setEavAttributeValue('title', 'دسکتاپ', 'fa');
        $product2->setEavAttributeValue('title', 'Desktop', 'de');

        // Search in English locale
        $results = TestProduct::whereEavAttribute('title', 'Laptop')->get();
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results->first()->id);

        // Search in Persian locale
        $results = TestProduct::whereEavAttribute('title', 'لپ تاپ')->get();
        $this->assertCount(1, $results);
        $this->assertEquals(1, $results->first()->id);
    }

    /** @test */
    public function it_can_search_by_attribute_value_like_in_specific_locale(): void
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
        $product1->setEavAttributeValue('title', 'Laptop Computer', 'en');
        $product1->setEavAttributeValue('title', 'رایانه لپ تاپ', 'fa');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('title', 'Desktop Computer', 'en');
        $product2->setEavAttributeValue('title', 'رایانه دسکتاپ', 'fa');

        // Search in English
        $results = TestProduct::whereEavAttributeLike('title', 'Computer')->get();
        $this->assertCount(2, $results);

        // Search in Persian
        $results = TestProduct::whereEavAttributeLike('title', 'رایانه')->get();
        $this->assertCount(2, $results);
    }

    /** @test */
    public function it_can_filter_by_number_range_in_different_locales(): void
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
        $product1->setEavAttributeValue('price', 100, 'en');
        $product1->setEavAttributeValue('price', 100, 'fa');
        $product1->setEavAttributeValue('price', 100, 'de');

        $product2 = new TestProduct();
        $product2->id = 2;
        $product2->save();
        $product2->setEavAttributeValue('price', 200, 'en');
        $product2->setEavAttributeValue('price', 200, 'fa');
        $product2->setEavAttributeValue('price', 200, 'de');

        $product3 = new TestProduct();
        $product3->id = 3;
        $product3->save();
        $product3->setEavAttributeValue('price', 300, 'en');
        $product3->setEavAttributeValue('price', 300, 'fa');
        $product3->setEavAttributeValue('price', 300, 'de');

        // Filter by number range (should work regardless of locale for numbers)
        $results = TestProduct::whereEavAttributeBetween('price', 150, 250)->get();
        $this->assertCount(1, $results);
        $this->assertEquals(2, $results->first()->id);
    }

    /** @test */
    public function it_can_handle_mixed_locale_and_non_locale_values(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $skuAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'SKU',
            'slug' => 'sku',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        // Set locale-based value
        $product->setEavAttributeValue('title', 'English Title', 'en');
        $product->setEavAttributeValue('title', 'عنوان فارسی', 'fa');

        // Set simple value (without locale - backward compatibility)
        $product->setEavAttributeValue('sku', 'SKU-123');

        // Get locale-based value
        $this->assertEquals('English Title', $product->getEavAttributeValue('title', 'en'));

        // Get simple value (should work with current locale)
        $this->assertEquals('SKU-123', $product->getEavAttributeValue('sku'));
    }

    /** @test */
    public function it_can_update_attribute_value_for_specific_locale(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        // Set initial values
        $product->setEavAttributeValue('title', 'Old English Title', 'en');
        $product->setEavAttributeValue('title', 'عنوان قدیمی', 'fa');

        // Update only English
        $product->setEavAttributeValue('title', 'New English Title', 'en');

        // Verify English updated, Persian unchanged
        $this->assertEquals('New English Title', $product->getEavAttributeValue('title', 'en'));
        $this->assertEquals('عنوان قدیمی', $product->getEavAttributeValue('title', 'fa'));
    }

    /** @test */
    public function it_can_get_values_for_current_locale_only(): void
    {
        app()->setLocale('fa');

        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        $product->setEavAttributeValue('title', 'English Title', 'en');
        $product->setEavAttributeValue('title', 'عنوان فارسی', 'fa');
        $product->setEavAttributeValue('title', 'Deutscher Titel', 'de');

        // Get values for current locale (fa)
        $values = $product->getEavAttributeValues('slug', 'fa');

        $this->assertCount(1, $values);
        $this->assertEquals('عنوان فارسی', $values['title']);
    }

    /** @test */
    public function it_handles_missing_locale_values_gracefully(): void
    {
        $product = new TestProduct();
        $product->id = 1;
        $product->save();

        $titleAttr = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Title',
            'slug' => 'title',
            'type' => AttributeType::TEXT->value,
            'is_active' => true,
            'language' => 'en',
        ]);

        // Set value only for English
        $product->setEavAttributeValue('title', 'English Title', 'en');

        // Try to get value for Persian (should return null)
        $this->assertNull($product->getEavAttributeValue('title', 'fa'));

        // Get value for English (should work)
        $this->assertEquals('English Title', $product->getEavAttributeValue('title', 'en'));
    }
}

