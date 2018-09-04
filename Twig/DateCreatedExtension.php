<?php

namespace Mongator\MongatorBundle\Twig;

use Mongator\MongatorBundle\Helper\DateCreatedHelper;

/**
 * @author nvb <nvb@aproxima.ru>
 *
 */
class DateCreatedExtension extends \Twig_Extension
{
    /**
     * @var DateCreatedHelper
     */
    private $helper;

    /**
     * @param DateCreatedHelper $helper
     */
    public function __construct(DateCreatedHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Twig_Extension::getFilters()
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('mongodb_created', [$this, 'getDateCreated'])
        ];
    }

    /**
     * @param \MongoDB\BSON\ObjectId|string $mongoId
     *
     * @return \DateTime
     */
    public function getDateCreated($mongoId)
    {
        try {
            return $this->helper->getDateCreated($mongoId);
        } catch (\Exception $e) {
            return null;
        }
    }
}
