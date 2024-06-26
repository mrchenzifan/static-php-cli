<?php

declare(strict_types=1);

namespace SPC\builder\unix\library;

use SPC\store\FileSystem;

trait brotli
{
    protected function build(): void
    {
        FileSystem::resetDir($this->source_dir . '/build-dir');
        shell()->cd($this->source_dir . '/build-dir')
            ->exec(
                $this->builder->configure_env . ' cmake ' .
                "{$this->builder->makeCmakeArgs()} " .
                '-DBUILD_SHARED_LIBS=OFF ' .
                '..'
            )
            ->exec("cmake --build . -j {$this->builder->concurrency}")
            ->exec('make install DESTDIR=' . BUILD_ROOT_PATH);
        $this->patchPkgconfPrefix(['libbrotlicommon.pc', 'libbrotlidec.pc', 'libbrotlienc.pc']);
        shell()->cd(BUILD_ROOT_PATH . '/lib')
            ->exec('ln -s libbrotlicommon-static.a libbrotlicommon.a')
            ->exec('ln -s libbrotlidec-static.a libbrotlidec.a')
            ->exec('ln -s libbrotlienc-static.a libbrotlienc.a');
        foreach (FileSystem::scanDirFiles(BUILD_ROOT_PATH . '/lib/', false, true) as $filename) {
            if (str_starts_with($filename, 'libbrotli') && (str_contains($filename, '.so') || str_ends_with($filename, '.dylib'))) {
                unlink(BUILD_ROOT_PATH . '/lib/' . $filename);
            }
        }
    }
}
