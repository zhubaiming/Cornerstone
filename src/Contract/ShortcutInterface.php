<?php

declare(strict_types=1);

namespace Zhubaiming\Cornerstone\Contract;

/**
 * 快捷方式
 * 是一系列 Plugin 的组合
 */
interface ShortcutInterface
{
    public function getPlugins(): array;
}