<?php

namespace Fiachehr\LaravelEav\Application\UseCases;

use Fiachehr\LaravelEav\Application\DTOs\CreateAttributeDTO;
use Fiachehr\LaravelEav\Domain\Entities\Attribute;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeRepositoryInterface;
use Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute;

class CreateAttributeUseCase
{
    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository
    ) {}

    public function execute(CreateAttributeDTO $dto, ?array $translations = null, ?callable $translationHandler = null): Attribute
    {
        $attribute = new Attribute(
            id: null,
            logicalId: $dto->logicalId,
            title: $dto->title,
            slug: $dto->slug,
            type: $dto->type,
            description: $dto->description,
            values: $dto->values,
            validations: $dto->validations,
            isActive: $dto->isActive,
            language: $dto->language,
        );

        $createdAttribute = $this->attributeRepository->create($attribute);

        // Handle translations if provided and handler is available
        if ($translations && $translationHandler && is_callable($translationHandler)) {
            $eloquentAttribute = EloquentAttribute::where('logical_id', $createdAttribute->logicalId)->first();
            if ($eloquentAttribute) {
                $translationHandler($eloquentAttribute, $translations);
            }
        }

        return $createdAttribute;
    }
}
