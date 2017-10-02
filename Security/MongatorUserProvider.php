<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\MongatorBundle\Security;

use Mongator\Mongator;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * MongatorUserProvider.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class MongatorUserProvider implements UserLoaderInterface
{
    private $mongator;
    private $class;
    private $property;

    /**
     * Constructor.
     *
     * @param Mongator    $mongator The mongator.
     * @param string      $class    The class.
     * @param string|null $property The property (optional).
     */
    public function __construct(Mongator $mongator, $class, $property = null)
    {
        $this->mongator = $mongator;
        $this->class = $class;
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $class = $this->class;
        $repository = $this->mongator->getRepository($class);

        if (null !== $this->property) {
            $user = $repository->createQuery(array($this->property => $username))->one();
        } else {
            if (!$repository instanceof UserProviderInterface) {
                throw new \InvalidArgumentException(sprintf('The Mongator repository "%s" must implement UserProviderInterface.', get_class($repository)));
            }

            $user = $repository->loadUserByUsername($username);
        }

        if (null === $user) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof $this->class) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->class;
    }
}
