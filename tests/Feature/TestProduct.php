<?php

namespace Fiachehr\LaravelEav\Tests\Feature;

use Fiachehr\LaravelEav\Domain\Shared\Traits\HasAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Test model for EAV tests
 */
class TestProduct extends Model
{
    use HasAttributes;

    protected $table = 'test_products';
    public $timestamps = false;

    protected $fillable = ['id'];
}

