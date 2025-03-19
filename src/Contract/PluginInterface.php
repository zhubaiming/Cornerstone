<?php

declare(strict_types=1);

namespace Zhubaiming\Cornerstone\Contract;

use Zhubaiming\Cornerstone\Patchwerk;

/**
 * 插件
 * 遵循 pipeline 的实现方式，内部定义每个插件的具体执行内容
 */
interface PluginInterface
{
    public function handle(Patchwerk $patchwerk, \Closure $next): Patchwerk;
}