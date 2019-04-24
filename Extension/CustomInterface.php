<?php

namespace Mongator\MongatorBundle\Extension;

use Mandango\Mondator\Extension;

/**
 * CustomInterface
 *
 * @author nvb <nvb@aproxima.ru>
 */
class CustomInterface extends Extension
{
    /**
     * {@inheritdoc}
     */
    protected function doClassProcess()
    {
        if (!empty($this->configClass['implements'])) {
            $implements = $this->configClass['implements'];

            $implements = is_array($implements) ?: [$implements];

            foreach ($implements as $interface) {
                $this->definitions['document_base']->addInterface('\\' . ltrim($interface, '\\'));
            }
        }
    }
}
