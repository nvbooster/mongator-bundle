<?php

namespace Mongator\MongatorBundle\Tests\Security {

    use Mongator\MongatorBundle\Security\MongatorUserProvider;

    class MongatorUserProviderTest extends \PHPUnit_Framework_TestCase
    {
        private $userClass;
        private $mongator;
        private $provider;

        protected function setUp()
        {
            $this->userClass = '\\Model\\BlogBundle\\User';
            $this->mongator = \Mockery::mock('Mongator\Mongator');

            $this->provider = new MongatorUserProvider(
                $this->mongator,
                $this->userClass
            );
        }

        public function testSupportsClass()
        {
            $this->assertTrue(
                $this->provider->supportsClass(
                    $this->userClass
                )
            );
            $this->assertFalse(
                $this->provider->supportsClass(
                    '\\Model\\BlogBundle\\OtherClass'
                )
            );
        }

        public function testRefreshUserShouldThrowExceptionIfNotUserInterface()
        {
            $this->setExpectedException('Symfony\Component\Security\Core\Exception\UnsupportedUserException');
            $user = \Mockery::mock('Symfony\Component\Security\Core\User\UserInterface');

            $this->provider->refreshUser($user);
        }

        public function testRefreshUser()
        {
            $user = \Mockery::mock($this->userClass);
            $user->shouldReceive('getUsername')
                ->once()
                ->andReturn('testUser');

            $this->provider = \Mockery::mock(
                'Mongator\MongatorBundle\Security\MongatorUserProvider',
                array(
                    $this->mongator,
                    $this->userClass
                )
            )->makePartial();

            $this->provider->shouldReceive('loadUserByUsername')
                ->once()
                ->andReturn($user);

            $this->assertEquals($user, $this->provider->refreshUSer($user));
        }

        public function testLoadUserByUsernameThrowExceptionIfNoUserProviderUsingDefaultProperty()
        {
            $this->mongator
                ->shouldReceive('getRepository')
                ->andReturn(new \stdClass());

            $this->setExpectedException('InvalidArgumentException');
            $this->provider->loadUserByUsername('testUser');
        }

        public function testLoadUserByUsernameUsingDefaultProperty()
        {
            $username = 'testUser';
            $user = new \stdClass();

            $repository = \Mockery::mock('Symfony\Component\Security\Core\User\UserProviderInterface');
            $repository->shouldReceive('loadUserByUsername')
                ->with($username)
                ->andReturn($user);

            $this->mongator
                ->shouldReceive('getRepository')
                ->andReturn($repository);

            $this->assertSame(
                $user,
                $this->provider->loadUserByUsername($username)
            );
        }

        public function testLoadUserByUsernameShouldThrowExceptionIfUserNotFound()
        {
            $username = 'testUser';

            $repository = \Mockery::mock('Symfony\Component\Security\Core\User\UserProviderInterface');
            $repository->shouldReceive('loadUserByUsername')
                ->with($username)
                ->andReturn(null);

            $this->mongator
                ->shouldReceive('getRepository')
                ->andReturn($repository);

            $this->setExpectedException('Symfony\Component\Security\Core\Exception\UsernameNotFoundException');
            $this->provider->loadUserByUsername($username);
        }

        public function testLoadUserByUsernameUsingCustomProperty()
        {
            $username = 'testUser';
            $property = 'account';
            $user = new \stdClass();

            $query = \Mockery::mock('\\Mongator\\Query\\Query')->makePartial();
            $query
                ->shouldReceive('all')
                ->andReturn(array($user));

            $repository = \Mockery::mock('\\Mongator\\Repository');
            $repository
                ->shouldReceive('createQuery')
                ->with(array($property => $username))
                ->andReturn($query);

            $this->mongator
                ->shouldReceive('getRepository')
                ->andReturn($repository);

            $this->provider = new MongatorUserProvider(
                $this->mongator,
                $this->userClass,
                $property
            );

            $this->assertSame(
                $user,
                $this->provider->loadUserByUsername($username)
            );
        }
    }
}


namespace Model\BlogBundle {

    abstract class User extends \Mongator\Document\Document
        implements \Symfony\Component\Security\Core\User\UserInterface
    {

    }
}
