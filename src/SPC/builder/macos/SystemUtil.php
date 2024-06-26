<?php

declare(strict_types=1);

namespace SPC\builder\macos;

use SPC\builder\traits\UnixSystemUtilTrait;
use SPC\exception\RuntimeException;
use SPC\exception\WrongUsageException;

class SystemUtil
{
    /** macOS 兼容 unix 的系统工具 */
    use UnixSystemUtilTrait;

    /**
     * 获取系统 CPU 逻辑内核数
     *
     * @throws RuntimeException
     */
    public static function getCpuCount(): int
    {
        [$ret, $output] = shell()->execWithResult('sysctl -n hw.ncpu');
        if ($ret !== 0) {
            throw new RuntimeException('Failed to get cpu count');
        }

        return (int) $output[0];
    }

    /**
     * 获取不同架构对应的 cflags 参数
     *
     * @param  string              $arch 架构名称
     * @throws WrongUsageException
     */
    public static function getArchCFlags(string $arch): string
    {
        return match ($arch) {
            'x86_64' => '--target=x86_64-apple-darwin',
            'arm64','aarch64' => '--target=arm64-apple-darwin',
            default => throw new WrongUsageException('unsupported arch: ' . $arch),
        };
    }
}
