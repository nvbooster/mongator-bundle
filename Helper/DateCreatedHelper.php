<?php
namespace Mongator\MongatorBundle\Helper;

use MongoDB\BSON\ObjectId;
use MongoDB\Driver\Exception\InvalidArgumentException as MongoDBInvalidArgumentException;

/**
 * @author nvb <nvb@aproxima.ru>
 *
 */
class DateCreatedHelper
{
    /**
     * @param ObjectId|string $mongoId
     *
     * @return \DateTime
     *
     * @throws \InvalidArgumentException
     */
    public function getDateCreated($mongoId)
    {
        if (!$mongoId) {
            return null;
        }

        if (is_string($mongoId)) {
            try {
                $mongoId = new ObjectId($mongoId);
            } catch (MongoDBInvalidArgumentException $e) {

            }
        }

        if (is_object($mongoId) && $mongoId instanceof ObjectId) {
            $createdDate = new \DateTime();
            $createdDate->setTimestamp($mongoId->getTimestamp());
            $createdDate->setTimezone(new \DateTimeZone(date_default_timezone_get()));

            return $createdDate;
        }

        throw new \InvalidArgumentException(sprintf(
            '%s is not an %s instance nor its string representation',
            is_object($mongoId) ? sprintf('object(%s)', get_class($mongoId)) : $mongoId,
            ObjectId::class
        ));
    }
}
