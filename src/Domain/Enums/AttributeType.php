<?php

namespace Fiachehr\LaravelEav\Domain\Enums;

enum AttributeType: int
{
    // Text types
    case TEXT = 0;
    case TEXTAREA = 1;
    case PASSWORD = 2;

        // Number types
    case NUMBER = 3;
    case DECIMAL = 4;

        // Selection types
    case RADIO = 5;
    case SELECT = 6;
    case MULTIPLE = 7;
    case CHECKBOX = 8;
    case COLOR = 9;

        // Date/Time types
    case DATE = 10;
    case TIME = 11;
    case DATETIME = 12;

        // Boolean types
    case BOOLEAN = 13;

        // File types
    case FILE = 14;

        // Location types
    case LOCATION = 15;
    case COORDINATES = 16;

    public function label(): string
    {
        return match ($this) {
            // Text
            self::TEXT => 'Text',
            self::TEXTAREA => 'Textarea',
            self::PASSWORD => 'Password',

            // Number
            self::NUMBER => 'Number',
            self::DECIMAL => 'Decimal',

            // Selection
            self::RADIO => 'Radio',
            self::SELECT => 'Select',
            self::MULTIPLE => 'Multiple Select',
            self::CHECKBOX => 'Checkbox',
            self::COLOR => 'Color',

            // Date/Time
            self::DATE => 'Date',
            self::TIME => 'Time',
            self::DATETIME => 'DateTime',

            // Boolean
            self::BOOLEAN => 'Boolean',

            // File
            self::FILE => 'File',

            // Location
            self::LOCATION => 'Location',
            self::COORDINATES => 'Coordinates',
        };
    }

    public function requiresValues(): bool
    {
        return in_array($this, [
            self::RADIO,
            self::SELECT,
            self::MULTIPLE,
            self::CHECKBOX,
            self::COLOR,
        ]);
    }

    public function getValueColumn(): string
    {
        return match ($this) {
            // Text types -> value_text
            self::TEXT, self::TEXTAREA, self::PASSWORD, self::LOCATION => 'value_text',

            // Number types -> value_number or value_decimal
            self::NUMBER => 'value_number',
            self::DECIMAL => 'value_decimal',

            // Date/Time types
            self::DATE => 'value_date',
            self::TIME => 'value_time',
            self::DATETIME => 'value_datetime',

            // Boolean types
            self::BOOLEAN, self::CHECKBOX => 'value_boolean',

            // Selection and file types -> value_json
            self::RADIO, self::SELECT, self::MULTIPLE, self::COLOR,
            self::FILE, self::COORDINATES => 'value_json',
        };
    }

    public function isSearchable(): bool
    {
        return in_array($this, [
            self::TEXT,
            self::TEXTAREA,
            self::LOCATION,
        ]);
    }

    public function isNumeric(): bool
    {
        return in_array($this, [
            self::NUMBER,
            self::DECIMAL,
        ]);
    }

    public function isDate(): bool
    {
        return in_array($this, [
            self::DATE,
            self::TIME,
            self::DATETIME,
        ]);
    }

    public function isBoolean(): bool
    {
        return in_array($this, [
            self::BOOLEAN,
            self::CHECKBOX,
        ]);
    }

    public static function casesAsArray(): array
    {
        return array_map(
            fn(self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
                'requires_values' => $type->requiresValues(),
                'value_column' => $type->getValueColumn(),
            ],
            self::cases()
        );
    }
}
