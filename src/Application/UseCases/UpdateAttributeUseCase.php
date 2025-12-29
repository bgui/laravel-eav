<?php

namespace Fiachehr\LaravelEav\Application\UseCases;

use Fiachehr\LaravelEav\Domain\Entities\Attribute;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeRepositoryInterface;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;

class UpdateAttributeUseCase
{
    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository
    ) {}

    public function execute(Attribute $attribute, ?array $translations = null, ?callable $translationHandler = null): Attribute
    {
        $updatedAttribute = $this->attributeRepository->update($attribute);

        // Handle translations if provided and handler is available
        if ($translations && $translationHandler && is_callable($translationHandler)) {
            $eloquentAttribute = EloquentAttribute::find($updatedAttribute->id);
            if ($eloquentAttribute) {
                $translationHandler($eloquentAttribute, $translations);
            }
        }

        return $updatedAttribute;
    }
}

