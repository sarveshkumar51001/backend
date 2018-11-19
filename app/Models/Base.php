<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

/**
 * Class Base
 * @package App\Models
 */
abstract class Base extends Eloquent
{
	const ID        = 'id';
	const CreatedAt = 'created_at';
	const UpdatedAt = 'updated_at';
	const CreatedBy = 'created_by';
	const UpdatedBy = 'updated_by';

	public $timestamps = false;
	public $perPage = 15;
}