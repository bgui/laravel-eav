# Laravel EAV Package - Test Suite

This test suite covers all features of the `laravel-eav` package.

## Test Structure

### Unit Tests (`tests/Unit/`)

#### Repositories
- **EloquentAttributeRepositoryTest**: CRUD tests for Attribute Repository
  - Create, Read, Update, Delete
  - Find by ID, Slug, Logical ID
  - Find by Language, Current Language, Multiple Languages
  - Find by Group IDs, Find by Slugs
  - Attach/Detach to Groups
  - Support for all Attribute Types

- **EloquentAttributeGroupRepositoryTest**: CRUD tests for Attribute Group Repository
  - Create, Read, Update, Delete
  - Find by ID, Slug
  - Find by Language, Current Language, Multiple Languages
  - Create with Attributes
  - Sync Attributes

#### Services
- **AttributeValidatorTest**: Validation Service tests
  - Convert Validation Types to Laravel Rules
  - Validation with Required, Min Length, Max Length
  - Validation with Email, URL, Regex
  - Validation for Numbers (Min, Max)
  - Validation for Files (Image, Max Size)
  - Validation for Dates (Date Format)
  - Handling Multiple Validations
  - Type-specific Default Rules

- **EavValidationServiceTest**: EAV Validation Service tests
  - Get Validation Rules by Group IDs
  - Get Validation Rules by Slugs
  - Validate EAV Attributes
  - Handle Attributes without Validations
  - Multiple Groups Support

#### Use Cases
- **CreateAttributeUseCaseTest**: Create Attribute Use Case tests
  - Create Attribute
  - Create with Translations
  - Create with Values
  - Create with Validations
  - Support for all Attribute Types

- **UpdateAttributeUseCaseTest**: Update Attribute Use Case tests
  - Update Attribute
  - Update with Translations

- **CreateAttributeGroupUseCaseTest**: Create Attribute Group Use Case tests
  - Create Group
  - Create with Attributes
  - Create with Module ID

### Feature Tests (`tests/Feature/`)

#### Traits
- **HasAttributesTraitTest**: HasAttributes Trait tests
  - Set and Get Attribute Values
  - Set Multiple Attribute Values
  - Get All Attribute Values
  - Sync Attribute Values
  - Remove Attribute Value
  - Clear All Attribute Values
  - Search by Attribute Value
  - Search by Attribute Value Like
  - Search by Number Range
  - Search by Date Range
  - Search by Multiple Attributes
  - Work with Attribute Groups
  - Handle Different Attribute Types (Text, Number, Decimal, Boolean, Date)

- **HasTranslationsTraitTest**: HasTranslations Trait tests
  - Set and Get Translations
  - Set Multiple Translations
  - Delete Translation
  - Delete Translations for Locale
  - Delete Translations for Key
  - Delete All Translations
  - Auto-delete Translations on Model Delete
  - Get All Translations
  - Get Translations for Locale
  - Get Translations for Key
  - Check if Translation Exists
  - Filter by Language
  - Filter by Current Language
  - Filter by Multiple Languages
  - Exclude Specific Language

#### Search & Query
- **MultilingualSearchTest**: Multilingual search tests
  - Search by Translated Attribute Title
  - Search Attributes by Language
  - Search with Multilingual Attribute Values
  - Search Across Multiple Languages
  - Search with Translated Attribute Names
  - Filter Attributes by Language and Search

- **EavQueryBuilderTest**: EAV Query Builder tests
  - Filter by Text Value
  - Filter by Text Like
  - Filter by Number Value
  - Filter by Number Range
  - Filter by Boolean Value
  - Filter by Date Range
  - Filter by Multiple Conditions
  - Get Count
  - Get Sum of Numeric Values
  - Get Average of Numeric Values

## Running Tests

### Installing Dependencies

```bash
cd packages/laravel-eav
composer install
```

### Running All Tests

```bash
./vendor/bin/phpunit
```

### Running Unit Tests

```bash
./vendor/bin/phpunit --testsuite=Unit
```

### Running Feature Tests

```bash
./vendor/bin/phpunit --testsuite=Feature
```

### Running a Specific Test

```bash
./vendor/bin/phpunit tests/Unit/Repositories/EloquentAttributeRepositoryTest.php
```

### Running a Specific Test Method

```bash
./vendor/bin/phpunit --filter it_can_create_an_attribute
```

## Test Coverage

### Repository Tests
- ✅ CRUD Operations
- ✅ Find Methods (by ID, Slug, Logical ID)
- ✅ Language-based Filtering
- ✅ Group Operations
- ✅ Multilingual Support

### Service Tests
- ✅ Validation Rules Conversion
- ✅ Validation Execution
- ✅ All Validation Types
- ✅ Type-specific Rules

### Use Case Tests
- ✅ Create Operations
- ✅ Update Operations
- ✅ Translation Support
- ✅ All Attribute Types

### Trait Tests
- ✅ HasAttributes: All CRUD and Search methods
- ✅ HasTranslations: All Translation methods
- ✅ Multilingual Support
- ✅ All Attribute Types Support

### Search Tests
- ✅ Text Search (Exact, Like)
- ✅ Number Search (Exact, Range)
- ✅ Date Search (Exact, Range)
- ✅ Boolean Search
- ✅ Multiple Conditions
- ✅ Multilingual Search
- ✅ Query Builder Methods

## Important Notes

1. All tests use SQLite in-memory database
2. Each test runs independently (RefreshDatabase)
3. Default locale for tests is `en`
4. Supported locales: `en`, `fa`, `de`, `es`

## Database Structure in Tests

- `attributes`: Main attributes table
- `attribute_groups`: Attribute groups table
- `attribute_group_attributes`: Pivot table for groups and attributes relationship
- `attributable_attributes`: Attribute values table
- `attributable_attribute_groups`: Pivot table for models and groups relationship
- `eav_translations`: Translations table
- `test_products`: Test table for models

## Usage Examples

### CRUD Test

```php
/** @test */
public function it_can_create_an_attribute(): void
{
    $attribute = new Attribute(...);
    $created = $this->repository->create($attribute);
    $this->assertNotNull($created->id);
}
```

### Validation Test

```php
/** @test */
public function it_validates_required_field(): void
{
    $this->expectException(ValidationException::class);
    $this->validator->validate(
        AttributeType::TEXT,
        '',
        ['required']
    );
}
```

### Multilingual Test

```php
/** @test */
public function it_can_set_and_get_translations(): void
{
    $attribute->setTranslation('title', 'fa', 'Persian Title');
    $this->assertEquals('Persian Title', $attribute->getTranslation('title', 'fa'));
}
```

### Search Test

```php
/** @test */
public function it_can_search_by_attribute_value(): void
{
    $results = Product::whereEavAttribute('color', 'red')->get();
    $this->assertCount(1, $results);
}
```

## Coverage

The tests cover all the following features:

- ✅ All Repository Methods
- ✅ All Service Methods
- ✅ All Use Case Methods
- ✅ All Trait Methods
- ✅ All Search Methods
- ✅ All Validation Types
- ✅ All Attribute Types
- ✅ Multilingual Support
- ✅ Error Handling
- ✅ Edge Cases
