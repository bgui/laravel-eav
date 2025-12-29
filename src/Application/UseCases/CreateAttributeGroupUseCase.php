<?php

namespace Fiachehr\LaravelEav\Application\UseCases;

use Fiachehr\LaravelEav\Application\DTOs\CreateAttributeGroupDTO;
use Fiachehr\LaravelEav\Domain\Entities\AttributeGroup;
use Fiachehr\LaravelEav\Domain\Repositories\AttributeGroupRepositoryInterface;

class CreateAttributeGroupUseCase
{
    public function __construct(
        private readonly AttributeGroupRepositoryInterface $groupRepository
    ) {}

    public function execute(CreateAttributeGroupDTO $dto): AttributeGroup
    {
        $group = new AttributeGroup(
            id: null,
            title: $dto->title,
            slug: $dto->slug,
            isActive: $dto->isActive,
            language: $dto->language,
            moduleId: $dto->moduleId,
            attributeIds: $dto->attributeIds,
        );

        return $this->groupRepository->create($group);
    }
}


