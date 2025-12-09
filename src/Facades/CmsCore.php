<?php

namespace Idoneo\CmsCore\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Idoneo\CmsCore\CmsCore
 */
class CmsCore extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return \Idoneo\CmsCore\CmsCore::class;
	}
}


