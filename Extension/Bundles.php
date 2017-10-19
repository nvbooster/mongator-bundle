<?php

namespace Mongator\MongatorBundle\Extension;

use Mandango\Mondator\Definition;
use Mandango\Mondator\Extension;
use Mandango\Mondator\Output;

/**
 * @author nvb <nvb@aproxima.ru>
 *
 */
class Bundles extends Extension
{
    /**
     * {@inheritdoc}
     */
    protected function doClassProcess()
    {
        foreach (['bundle_name', 'bundle_namespace', 'bundle_output'] as $parameter) {
            if (!isset($this->configClass[$parameter]) || !$this->configClass[$parameter]) {
                return;
            }
        }

        $generateIntermediate = $this->configClass['bundle_models'];

        if (!isset($this->configClass['model_namespace'])) {
            $this->configClass['model_namespace'] = 'Model';
        }

        $relCN = $this->configClass['model_namespace'].'\\'.substr($this->class, strrpos($this->class, '\\') + 1);
        $fullCN = $this->configClass['bundle_namespace'].'\\'.$relCN;

        /*
         * Definitions.
         */
        $classes = [
            'document_bundle'   => $fullCN,
            'repository_bundle' => $fullCN.'Repository',
            'query_bundle'      => $fullCN.'Query',
        ];

        $relPath = str_replace('\\', DIRECTORY_SEPARATOR, $relCN);

        $paths = [
            'document_bundle'   => $relPath.'.php',
            'repository_bundle' => $relPath.'Repository.php',
            'query_bundle'      => $relPath.'Query.php',
        ];



        // document
        if (file_exists($this->configClass['bundle_output'].DIRECTORY_SEPARATOR.$paths['document_bundle'])) {
            $this->definitions['document']->setParentClass('\\'.$classes['document_bundle']);
        } elseif ($generateIntermediate) {
            $this->definitions['document']->setParentClass('\\'.$classes['document_bundle']);

            $output = new Output($this->configClass['bundle_output']);
            $this->definitions['document_bundle'] = new Definition($classes['document_bundle'], $output, $paths['document_bundle']);
            $this->definitions['document_bundle']->setParentClass('\\'.$this->definitions['document_base']->getClass());
            $this->definitions['document_bundle']->setAbstract(true);
            $this->definitions['document_bundle']->setDocComment(<<<EOF
/**
 * {$this->class} bundle document.
 */
EOF
            );
        }

        if (!$this->configClass['isEmbedded']) {
            // repository
            if (file_exists($this->configClass['bundle_output'].DIRECTORY_SEPARATOR.$paths['repository_bundle'])) {
                $this->definitions['repository']->setParentClass('\\'.$classes['repository_bundle']);
            } elseif ($generateIntermediate) {
                $this->definitions['repository']->setParentClass('\\'.$classes['repository_bundle']);

                $output = new Output($this->configClass['bundle_output']);
                $this->definitions['repository_bundle'] = new Definition($classes['repository_bundle'], $output, $paths['repository_bundle']);
                $this->definitions['repository_bundle']->setParentClass('\\'.$this->definitions['repository_base']->getClass());
                $this->definitions['repository_bundle']->setDocComment(<<<EOF
/**
 * {$this->class} bundle document repository.
 */
EOF
                );
            }

            // query
            if (file_exists($this->configClass['bundle_output'].DIRECTORY_SEPARATOR.$paths['query_bundle'])) {
                $this->definitions['query']->setParentClass('\\'.$classes['query_bundle']);
            } elseif ($generateIntermediate) {
                $this->definitions['query']->setParentClass('\\'.$classes['query_bundle']);

                $output = new Output($this->configClass['bundle_output']);
                $this->definitions['query_bundle'] = new Definition($classes['query_bundle'], $output, $paths['query_bundle']);
                $this->definitions['query_bundle']->setParentClass('\\'.$this->definitions['query_base']->getClass());
                $this->definitions['query_bundle']->setDocComment(<<<EOF
/**
 * {$this->class} bundle document query.
 */
EOF
                );
            }
        }
    }
}
