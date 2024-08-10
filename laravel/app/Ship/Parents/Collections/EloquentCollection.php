<?php declare(strict_types=1);

namespace App\Ship\Parents\Collections;

use Illuminate\Database\Eloquent\Collection as LaravelEloquentCollection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TKey of array-key
 * @template TModel of Model
 *
 * @extends LaravelEloquentCollection<TKey, TModel>
 */
abstract class EloquentCollection extends LaravelEloquentCollection
{
}
