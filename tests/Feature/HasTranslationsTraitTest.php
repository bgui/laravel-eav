<?php

namespace Fiachehr\LaravelEav\Tests\Feature;

use Fiachehr\LaravelEav\Domain\Shared\Traits\HasTranslations;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentTranslation;
use Fiachehr\LaravelEav\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HasTranslationsTraitTest extends TestCase
{
    /** @test */
    public function it_can_set_and_get_translations(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslation('title', 'fa', 'عنوان فارسی');
        $attribute->setTranslation('title', 'de', 'Deutscher Titel');

        // For default language (en), use the model's title field directly
        $this->assertEquals('English Title', $attribute->title);
        $this->assertEquals('عنوان فارسی', $attribute->getTranslation('title', 'fa'));
        $this->assertEquals('Deutscher Titel', $attribute->getTranslation('title', 'de'));
    }

    /** @test */
    public function it_can_set_multiple_translations_at_once(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslations([
            'title' => [
                'en' => 'English Title',
                'fa' => 'عنوان فارسی',
                'de' => 'Deutscher Titel',
            ],
            'description' => [
                'en' => 'English Description',
                'fa' => 'توضیحات فارسی',
            ],
        ]);

        $this->assertEquals('English Title', $attribute->getTranslation('title', 'en'));
        $this->assertEquals('عنوان فارسی', $attribute->getTranslation('title', 'fa'));
        $this->assertEquals('Deutscher Titel', $attribute->getTranslation('title', 'de'));
        $this->assertEquals('English Description', $attribute->getTranslation('description', 'en'));
        $this->assertEquals('توضیحات فارسی', $attribute->getTranslation('description', 'fa'));
    }

    /** @test */
    public function it_can_delete_translation(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslation('title', 'fa', 'عنوان فارسی');
        $this->assertEquals('عنوان فارسی', $attribute->getTranslation('title', 'fa'));

        $attribute->deleteTranslation('title', 'fa');
        $this->assertNull($attribute->getTranslation('title', 'fa'));
    }

    /** @test */
    public function it_can_delete_all_translations_for_locale(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslation('title', 'fa', 'عنوان فارسی');
        $attribute->setTranslation('description', 'fa', 'توضیحات فارسی');

        $attribute->deleteTranslationsForLocale('fa');

        $this->assertNull($attribute->getTranslation('title', 'fa'));
        $this->assertNull($attribute->getTranslation('description', 'fa'));
    }

    /** @test */
    public function it_can_delete_all_translations_for_key(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslation('title', 'fa', 'عنوان فارسی');
        $attribute->setTranslation('title', 'de', 'Deutscher Titel');
        $attribute->setTranslation('description', 'fa', 'توضیحات فارسی');

        $attribute->deleteTranslationsForKey('title');

        $this->assertNull($attribute->getTranslation('title', 'fa'));
        $this->assertNull($attribute->getTranslation('title', 'de'));
        $this->assertEquals('توضیحات فارسی', $attribute->getTranslation('description', 'fa'));
    }

    /** @test */
    public function it_can_delete_all_translations(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslation('title', 'fa', 'عنوان فارسی');
        $attribute->setTranslation('description', 'fa', 'توضیحات فارسی');

        $attribute->deleteAllTranslations();

        $this->assertNull($attribute->getTranslation('title', 'fa'));
        $this->assertNull($attribute->getTranslation('description', 'fa'));
    }

    /** @test */
    public function it_automatically_deletes_translations_when_model_is_deleted(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslation('title', 'fa', 'عنوان فارسی');

        $translationId = $attribute->translations()->first()->id;
        $attribute->delete();

        $this->assertNull(EloquentTranslation::find($translationId));
    }

    /** @test */
    public function it_can_get_all_translations(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslations([
            'title' => [
                'en' => 'English Title',
                'fa' => 'عنوان فارسی',
                'de' => 'Deutscher Titel',
            ],
            'description' => [
                'en' => 'English Description',
                'fa' => 'توضیحات فارسی',
            ],
        ]);

        $all = $attribute->getAllTranslations();

        $this->assertArrayHasKey('en', $all);
        $this->assertArrayHasKey('fa', $all);
        $this->assertArrayHasKey('de', $all);
        $this->assertEquals('English Title', $all['en']['title']);
        $this->assertEquals('عنوان فارسی', $all['fa']['title']);
        $this->assertEquals('Deutscher Titel', $all['de']['title']);
    }

    /** @test */
    public function it_can_get_translations_for_specific_locale(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslations([
            'title' => [
                'en' => 'English Title',
                'fa' => 'عنوان فارسی',
                'de' => 'Deutscher Titel',
            ],
        ]);

        $faTranslations = $attribute->getTranslationsForLocale('fa');

        $this->assertArrayHasKey('title', $faTranslations);
        $this->assertEquals('عنوان فارسی', $faTranslations['title']);
    }

    /** @test */
    public function it_can_get_translations_for_specific_key(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslations([
            'title' => [
                'en' => 'English Title',
                'fa' => 'عنوان فارسی',
                'de' => 'Deutscher Titel',
            ],
        ]);

        $titleTranslations = $attribute->getTranslationsForKey('title');

        $this->assertArrayHasKey('en', $titleTranslations);
        $this->assertArrayHasKey('fa', $titleTranslations);
        $this->assertArrayHasKey('de', $titleTranslations);
        $this->assertEquals('English Title', $titleTranslations['en']);
        $this->assertEquals('عنوان فارسی', $titleTranslations['fa']);
    }

    /** @test */
    public function it_can_check_if_translation_exists(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslation('title', 'fa', 'عنوان فارسی');

        $this->assertTrue($attribute->hasTranslation('fa'));
        $this->assertFalse($attribute->hasTranslation('de'));
    }

    /** @test */
    public function it_can_check_if_translation_exists_for_key(): void
    {
        $attribute = EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Title',
            'slug' => 'test-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        $attribute->setTranslation('title', 'fa', 'عنوان فارسی');

        $this->assertTrue($attribute->hasTranslationForKey('title', 'fa'));
        $this->assertFalse($attribute->hasTranslationForKey('title', 'de'));
        $this->assertFalse($attribute->hasTranslationForKey('description', 'fa'));
    }

    /** @test */
    public function it_can_filter_by_language(): void
    {
        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Attribute',
            'slug' => 'english-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Persian Attribute',
            'slug' => 'persian-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'fa',
        ]);

        $english = EloquentAttribute::where('language', 'en')->get();
        $persian = EloquentAttribute::where('language', 'fa')->get();

        $this->assertCount(1, $english);
        $this->assertEquals('English Attribute', $english->first()->title);
        $this->assertCount(1, $persian);
        $this->assertEquals('Persian Attribute', $persian->first()->title);
    }

    /** @test */
    public function it_can_filter_by_current_language(): void
    {
        app()->setLocale('en');

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Current Language',
            'slug' => 'current-language',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Other Language',
            'slug' => 'other-language',
            'type' => 0,
            'is_active' => true,
            'language' => 'fa',
        ]);

        $current = EloquentAttribute::where('language', app()->getLocale())->get();

        $this->assertCount(1, $current);
        $this->assertEquals('Current Language', $current->first()->title);
    }

    /** @test */
    public function it_can_filter_by_multiple_languages(): void
    {
        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Attribute',
            'slug' => 'english-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Persian Attribute',
            'slug' => 'persian-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'fa',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'German Attribute',
            'slug' => 'german-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'de',
        ]);

        $multi = EloquentAttribute::whereIn('language', ['en', 'fa'])->get();

        $this->assertCount(2, $multi);
        $this->assertTrue($multi->contains('title', 'English Attribute'));
        $this->assertTrue($multi->contains('title', 'Persian Attribute'));
        $this->assertFalse($multi->contains('title', 'German Attribute'));
    }

    /** @test */
    public function it_can_exclude_specific_language(): void
    {
        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'English Attribute',
            'slug' => 'english-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'en',
        ]);

        EloquentAttribute::create([
            'logical_id' => Str::uuid()->toString(),
            'title' => 'Persian Attribute',
            'slug' => 'persian-attribute',
            'type' => 0,
            'is_active' => true,
            'language' => 'fa',
        ]);

        $excluded = EloquentAttribute::where('language', '!=', 'en')->get();

        $this->assertCount(1, $excluded);
        $this->assertEquals('Persian Attribute', $excluded->first()->title);
    }
}

