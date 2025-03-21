<?php

declare(strict_types=1);

namespace Zhubaiming\Cornerstone\Plugin;

use Zhubaiming\Cornerstone\Contract\PluginInterface;
use Zhubaiming\Cornerstone\Patchwerk;

/**
 * 装载雷达插件
 *
 * 作用: 向 Patchwerk 中设置雷达(即: 请求类)，以及雷达的各项请求属性，以方便后续直接调用请求
 */
class AddRadarPlugin implements PluginInterface
{
    public function handle(Patchwerk $patchwerk, \Closure $next): Patchwerk
    {
        // 1、获取请求参数
        $parameters = $patchwerk->getParameters();

        // 2、获取请求荷载
        $payload = $patchwerk->getPayload();

        // 3、设置雷达
        $patchwerk->setRadar(new Request(xxx));


        return $next($patchwerk);
    }
}