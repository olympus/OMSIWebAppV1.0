<?php

namespace App\View\Compilers;

use ErrorException;
use Illuminate\View\Compilers\BladeCompiler as BaseBladeCompiler;
use Throwable;

class BladeCompiler extends BaseBladeCompiler
{
    /**
     * Laravel 13 bumps compiled-view mtimes with touch(). That fails when
     * PHP-FPM does not own files created by a root deploy, and becomes a 500.
     */
    public function compile($path = null): void
    {
        try {
            parent::compile($path);
        } catch (ErrorException $exception) {
            if (! str_contains($exception->getMessage(), 'Utime failed')) {
                throw $exception;
            }

            $this->rebuildCompiledView($path);
        }
    }

    private function rebuildCompiledView(?string $path): void
    {
        try {
            if ($path) {
                $this->setPath($path);
            }

            $compiledPath = $this->getCompiledPath($this->getPath());

            if ($this->files->exists($compiledPath)) {
                $this->files->delete($compiledPath);
            }

            parent::compile($path);
        } catch (Throwable) {
            // Compiled content is already valid; skip the mtime bump.
        }
    }
}
