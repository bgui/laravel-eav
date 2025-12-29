<?php

namespace Fiachehr\LaravelEav\Domain\ValueObjects;

use Fiachehr\LaravelEav\Domain\Enums\ValidationType;
use Fiachehr\LaravelEav\Domain\Services\AttributeValidator;

class AttributeValidation
{
    public function __construct(
        public readonly array $rules,
    ) {}

    public function toArray(): array
    {
        return $this->rules;
    }

    public function hasRule(string|ValidationType $rule): bool
    {
        $ruleValue = $rule instanceof ValidationType ? $rule->value : $rule;
        
        foreach ($this->rules as $validation) {
            if (is_array($validation)) {
                $type = $validation['type'] ?? null;
                if ($type === $ruleValue) {
                    return true;
                }
            } elseif ($validation === $ruleValue) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get Laravel validation rules for this attribute
     *
     * @param \Fiachehr\LaravelEav\Domain\Enums\AttributeType $attributeType
     * @return array
     */
    public function getLaravelRules(\Fiachehr\LaravelEav\Domain\Enums\AttributeType $attributeType): array
    {
        $validator = new AttributeValidator();
        return $validator->getValidationRules($attributeType, $this->rules);
    }
}


