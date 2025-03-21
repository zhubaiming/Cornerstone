<?php

namespace Zhubaiming\Cornerstone\Plugin;

use Zhubaiming\Cornerstone\Contract\PluginInterface;
use Zhubaiming\Cornerstone\Patchwerk;

/**
 * 处理返回结果插件
 *
 * 作用: 根据给定的 HTTP 状态码枚举，判定返回状态，并根据返回结果及返回格式，进行结果内容解析
 * 融合了原来的 ParsePlugin 插件
 *
 * 流程: 先向下执行，等待请求返回后，处理返回内容
 */
class ResponsePlugin implements PluginInterface
{
    public function handle(Patchwerk $patchwerk, \Closure $next): Patchwerk
    {
        // 获取后续请求过后，经过其他处理的类
        $patchwerk = $next($patchwerk);

        return $patchwerk;
    }
}