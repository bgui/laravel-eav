<?php

namespace Fiachehr\LaravelEav\Domain\Services;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Enums\ValidationType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AttributeValidator
{
    /**
     * Convert attribute validations to Laravel validation rules
     *
     * @param AttributeType $attributeType
     * @param array $validations Array of validation types with optional parameters
     * @return array Laravel validation rules
     */
    public function getValidationRules(AttributeType $attributeType, array $validations): array
    {
        $rules = [];
        
        foreach ($validations as $validation) {
            $validationType = $this->parseValidation($validation);
            $rule = $this->convertToLaravelRule($validationType, $validation);
            
            if ($rule) {
                $rules[] = $rule;
            }
        }
        
        // Add type-specific default rules
        $rules = array_merge($rules, $this->getDefaultRulesForType($attributeType));
        
        return $rules;
    }
    
    /**
     * Validate a value against attribute validations
     *
     * @param AttributeType $attributeType
     * @param mixed $value
     * @param array $validations
     * @return void
     * @throws ValidationException
     */
    public function validate(AttributeType $attributeType, mixed $value, array $validations): void
    {
        $rules = $this->getValidationRules($attributeType, $validations);
        
        $validator = Validator::make(
            ['value' => $value],
            ['value' => $rules]
        );
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
    
    /**
     * Parse validation string or array to ValidationType
     *
     * @param string|array $validation
     * @return ValidationType|null
     */
    protected function parseValidation(string|array $validation): ?ValidationType
    {
        if (is_array($validation)) {
            $type = $validation['type'] ?? null;
            if (!$type) {
                return null;
            }
            
            try {
                return ValidationType::from($type);
            } catch (\ValueError $e) {
                return null;
            }
        }
        
        try {
            return ValidationType::from($validation);
        } catch (\ValueError $e) {
            return null;
        }
    }
    
    /**
     * Convert ValidationType to Laravel validation rule
     *
     * @param ValidationType|null $validationType
     * @param string|array $validation Original validation data
     * @return string|array|null
     */
    protected function convertToLaravelRule(?ValidationType $validationType, string|array $validation): string|array|null
    {
        if (!$validationType) {
            return null;
        }
        
        $laravelRule = $validationType->getLaravelRule();
        
        // Handle validations that require parameters
        if ($validationType->requiresParameter()) {
            $parameter = $this->getValidationParameter($validation);
            
            if ($parameter !== null) {
                // Special handling for specific rules
                return match ($validationType) {
                    ValidationType::MIN_LENGTH, ValidationType::MAX_LENGTH => $laravelRule . ':' . $parameter,
                    ValidationType::MIN, ValidationType::MAX => $laravelRule . ':' . $parameter,
                    ValidationType::REGEX => $laravelRule . ':' . $parameter,
                    ValidationType::MAX_FILE_SIZE => $laravelRule . ':' . ($parameter * 1024), // Convert KB to bytes
                    ValidationType::ALLOWED_MIME_TYPES => $laravelRule . ':' . (is_array($parameter) ? implode(',', $parameter) : $parameter),
                    ValidationType::DATE_FORMAT => $laravelRule . ':' . $parameter,
                    ValidationType::AFTER, ValidationType::BEFORE => $laravelRule . ':' . $parameter,
                    default => $laravelRule,
                };
            }
        }
        
        return $laravelRule;
    }
    
    /**
     * Get validation parameter from validation data
     *
     * @param string|array $validation
     * @return mixed
     */
    protected function getValidationParameter(string|array $validation): mixed
    {
        if (is_array($validation)) {
            return $validation['parameter'] ?? $validation['value'] ?? null;
        }
        
        return null;
    }
    
    /**
     * Get default validation rules for attribute type
     *
     * @param AttributeType $attributeType
     * @return array
     */
    protected function getDefaultRulesForType(AttributeType $attributeType): array
    {
        return match ($attributeType) {
            AttributeType::NUMBER => ['numeric', 'integer'],
            AttributeType::DECIMAL => ['numeric'],
            AttributeType::BOOLEAN, AttributeType::CHECKBOX => ['boolean'],
            AttributeType::DATE => ['date'],
            AttributeType::TIME => ['date_format:H:i:s'],
            AttributeType::DATETIME => ['date'],
            AttributeType::FILE => ['file'],
            AttributeType::TEXT, AttributeType::TEXTAREA, AttributeType::PASSWORD => ['string'],
            AttributeType::LOCATION => ['string'],
            default => [],
        };
    }
    
    /**
     * Get available validations for an attribute type
     *
     * @param AttributeType $attributeType
     * @return array
     */
    public function getAvailableValidations(AttributeType $attributeType): array
    {
        return ValidationType::getByAttributeType($attributeType);
    }
}

