<?php

declare(strict_types=1);

namespace Zhubaiming\Cornerstone\Plugin;

use Zhubaiming\Cornerstone\Contract\PluginInterface;
use Zhubaiming\Cornerstone\Patchwerk;

/**
 * 起始插件
 *
 * 作用: 将「输入参数」合并到「有效荷载」
 */
class StartPlugin implements PluginInterface
{
    public function handle(Patchwerk $patchwerk, \Closure $next): Patchwerk
    {
        $patchwerk->setParametersOrigin($patchwerk->getParameters());

        return $next($patchwerk);
    }
}