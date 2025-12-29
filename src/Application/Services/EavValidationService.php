<?php

namespace Fiachehr\LaravelEav\Application\Services;

use Fiachehr\LaravelEav\Domain\Enums\AttributeType;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeRepositoryInterface;
use Fiachehr\LaravelEav\Domain\Services\AttributeValidator;
use Illuminate\Support\Collection;

class EavValidationService
{
    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly AttributeValidator $attributeValidator
    ) {}

    /**
     * Get validation rules for EAV attributes based on attribute groups
     *
     * @param array $attributeGroupIds
     * @param string|null $modelClass
     * @return array
     */
    public function getValidationRules(array $attributeGroupIds, ?string $modelClass = null): array
    {
        $rules = [];
        
        if (empty($attributeGroupIds)) {
            return $rules;
        }
        
        // Get all attributes from the selected groups
        $attributes = $this->attributeRepository->findByGroupIds($attributeGroupIds);
        
        foreach ($attributes as $attribute) {
            $fieldName = 'eav_attributes.' . $attribute->slug;
            $attributeType = $attribute->type instanceof AttributeType 
                ? $attribute->type 
                : AttributeType::from($attribute->type);
            
            $validations = $attribute->validations ?? [];
            
            if (empty($validations)) {
                continue;
            }
            
            // Convert EAV validations to Laravel rules
            $laravelRules = $this->attributeValidator->getValidationRules($attributeType, $validations);
            
            if (!empty($laravelRules)) {
                $rules[$fieldName] = $laravelRules;
            }
        }
        
        return $rules;
    }
    
    /**
     * Get validation rules for EAV attributes based on attribute slugs
     *
     * @param array $attributeSlugs
     * @return array
     */
    public function getValidationRulesBySlugs(array $attributeSlugs): array
    {
        $rules = [];
        
        if (empty($attributeSlugs)) {
            return $rules;
        }
        
        // Get attributes by slugs
        $attributes = $this->attributeRepository->findBySlugs($attributeSlugs);
        
        foreach ($attributes as $attribute) {
            $fieldName = 'eav_attributes.' . $attribute->slug;
            $attributeType = $attribute->type instanceof AttributeType 
                ? $attribute->type 
                : AttributeType::from($attribute->type);
            
            $validations = $attribute->validations ?? [];
            
            if (empty($validations)) {
                continue;
            }
            
            // Convert EAV validations to Laravel rules
            $laravelRules = $this->attributeValidator->getValidationRules($attributeType, $validations);
            
            if (!empty($laravelRules)) {
                $rules[$fieldName] = $laravelRules;
            }
        }
        
        return $rules;
    }
    
    /**
     * Validate EAV attribute values
     *
     * @param array $eavAttributes Array of attribute values keyed by slug
     * @param array $attributeGroupIds
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validate(array $eavAttributes, array $attributeGroupIds): \Illuminate\Contracts\Validation\Validator
    {
        $rules = $this->getValidationRules($attributeGroupIds);
        
        return \Illuminate\Support\Facades\Validator::make(
            ['eav_attributes' => $eavAttributes],
            $rules
        );
    }
}

