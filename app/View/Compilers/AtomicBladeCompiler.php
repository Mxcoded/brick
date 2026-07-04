<?php

namespace App\View\Compilers;

use Illuminate\View\Compilers\BladeCompiler;

class AtomicBladeCompiler extends BladeCompiler
{
    public function compile($path = null)
    {
        if ($path) {
            $this->setPath($path);
        }

        if (! is_null($this->cachePath)) {
            $contents = $this->compileString($this->files->get($this->getPath()));

            if (! empty($this->getPath())) {
                $contents = $this->appendFilePath($contents);
            }

            $this->ensureCompiledDirectoryExists(
                $compiledPath = $this->getCompiledPath($this->getPath())
            );

            $this->files->replace($compiledPath, $contents);
        }
    }
}
