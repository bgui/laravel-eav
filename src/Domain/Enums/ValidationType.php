<?php

namespace Fiachehr\LaravelEav\Domain\Enums;

enum ValidationType: string
{
    // Text validations
    case REQUIRED = 'required';
    case MIN_LENGTH = 'min_length';
    case MAX_LENGTH = 'max_length';
    case EMAIL = 'email';
    case URL = 'url';
    case SLUG = 'slug';
    case PASSWORD = 'password';
    case REGEX = 'regex';
    
    // Number validations
    case MIN = 'min';
    case MAX = 'max';
    case INTEGER = 'integer';
    case DECIMAL = 'decimal';
    
    // File validations
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';
    case MAX_FILE_SIZE = 'max_file_size';
    case ALLOWED_MIME_TYPES = 'allowed_mime_types';
    
    // Format validations
    case JSON = 'json';
    case ARRAY = 'array';
    case RICH_TEXT = 'rich_text';
    case MARKDOWN = 'markdown';
    
    // Date validations
    case DATE_FORMAT = 'date_format';
    case AFTER = 'after';
    case BEFORE = 'before';
    
    public function label(): string
    {
        return match ($this) {
            self::REQUIRED => 'Required',
            self::MIN_LENGTH => 'Min Length',
            self::MAX_LENGTH => 'Max Length',
            self::EMAIL => 'Email',
            self::URL => 'URL',
            self::SLUG => 'Slug',
            self::PASSWORD => 'Password',
            self::REGEX => 'Regex Pattern',
            self::MIN => 'Min Value',
            self::MAX => 'Max Value',
            self::INTEGER => 'Integer',
            self::DECIMAL => 'Decimal',
            self::IMAGE => 'Image',
            self::VIDEO => 'Video',
            self::AUDIO => 'Audio',
            self::DOCUMENT => 'Document',
            self::MAX_FILE_SIZE => 'Max File Size',
            self::ALLOWED_MIME_TYPES => 'Allowed MIME Types',
            self::JSON => 'JSON',
            self::ARRAY => 'Array',
            self::RICH_TEXT => 'Rich Text',
            self::MARKDOWN => 'Markdown',
            self::DATE_FORMAT => 'Date Format',
            self::AFTER => 'After Date',
            self::BEFORE => 'Before Date',
        };
    }
    
    public function getLaravelRule(): string
    {
        return match ($this) {
            self::REQUIRED => 'required',
            self::MIN_LENGTH => 'min',
            self::MAX_LENGTH => 'max',
            self::EMAIL => 'email',
            self::URL => 'url',
            self::SLUG => 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            self::PASSWORD => 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/',
            self::REGEX => 'regex',
            self::MIN => 'min',
            self::MAX => 'max',
            self::INTEGER => 'integer',
            self::DECIMAL => 'numeric',
            self::IMAGE => 'image',
            self::VIDEO => 'mimes:mp4,avi,mov,wmv,flv,webm',
            self::AUDIO => 'mimes:mp3,wav,ogg,aac,flac',
            self::DOCUMENT => 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            self::MAX_FILE_SIZE => 'max',
            self::ALLOWED_MIME_TYPES => 'mimes',
            self::JSON => 'json',
            self::ARRAY => 'array',
            self::RICH_TEXT => 'string',
            self::MARKDOWN => 'string',
            self::DATE_FORMAT => 'date_format',
            self::AFTER => 'after',
            self::BEFORE => 'before',
        };
    }
    
    public function requiresParameter(): bool
    {
        return in_array($this, [
            self::MIN_LENGTH,
            self::MAX_LENGTH,
            self::MIN,
            self::MAX,
            self::REGEX,
            self::MAX_FILE_SIZE,
            self::ALLOWED_MIME_TYPES,
            self::DATE_FORMAT,
            self::AFTER,
            self::BEFORE,
        ]);
    }
    
    public static function getByAttributeType(AttributeType $attributeType): array
    {
        return match ($attributeType) {
            AttributeType::TEXT, AttributeType::TEXTAREA => [
                self::REQUIRED,
                self::MIN_LENGTH,
                self::MAX_LENGTH,
                self::EMAIL,
                self::URL,
                self::SLUG,
                self::REGEX,
            ],
            AttributeType::PASSWORD => [
                self::REQUIRED,
                self::MIN_LENGTH,
                self::MAX_LENGTH,
                self::PASSWORD,
            ],
            AttributeType::NUMBER, AttributeType::DECIMAL => [
                self::REQUIRED,
                self::MIN,
                self::MAX,
                self::INTEGER,
                self::DECIMAL,
            ],
            AttributeType::FILE => [
                self::REQUIRED,
                self::IMAGE,
                self::VIDEO,
                self::AUDIO,
                self::DOCUMENT,
                self::MAX_FILE_SIZE,
                self::ALLOWED_MIME_TYPES,
            ],
            AttributeType::DATE, AttributeType::TIME, AttributeType::DATETIME => [
                self::REQUIRED,
                self::DATE_FORMAT,
                self::AFTER,
                self::BEFORE,
            ],
            default => [
                self::REQUIRED,
            ],
        };
    }
}

