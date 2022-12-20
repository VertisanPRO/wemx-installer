<?php

namespace Billing\Commands;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Billing\Commands\Skeleton\SkeletonClass
 */
class CommandsFacade extends Facade
{
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor()
  {
    return 'commands';
  }
}
