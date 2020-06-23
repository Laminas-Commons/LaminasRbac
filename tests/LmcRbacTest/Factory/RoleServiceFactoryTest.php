<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace LmcRbacTest\Factory;

use Laminas\ServiceManager\ServiceManager;
use LmcRbac\Factory\RoleServiceFactory;
use LmcRbac\Options\ModuleOptions;
use LmcRbac\Role\RoleProviderPluginManager;

/**
 * @covers \LmcRbac\Factory\RoleServiceFactory
 */
class RoleServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $options = new ModuleOptions([
            'identity_provider'    => 'LmcRbac\Identity\AuthenticationProvider',
            'guest_role'           => 'guest',
            'role_provider'        => [
                'LmcRbac\Role\InMemoryRoleProvider' => [
                    'foo'
                ]
            ]
        ]);

        $traversalStrategy = $this->getMock('Rbac\Traversal\Strategy\TraversalStrategyInterface');
        $roleProvider = $this->getMock('\LmcRbac\Role\RoleProviderInterface');

        $rbac = $this
            ->getMockBuilder('Rbac\Rbac')
            ->disableOriginalConstructor()
            ->getMock();
        $rbac->expects($this->once())
            ->method('getTraversalStrategy')
            ->will($this->returnValue(
                $traversalStrategy
            ));

        $pluginManager = $this
            ->getMockBuilder('\LmcRbac\Role\RoleProviderPluginManager')
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects($this->once())
            ->method('get')
            ->with('LmcRbac\Role\InMemoryRoleProvider', ['foo'])
            ->will($this->returnValue(
                $roleProvider
            ));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('LmcRbac\Options\ModuleOptions', $options);
        $serviceManager->setService('Rbac\Rbac', $rbac);
        $serviceManager->setService('LmcRbac\Role\RoleProviderPluginManager', $pluginManager);
        $serviceManager->setService('LmcRbac\Identity\AuthenticationProvider', $this->getMock('LmcRbac\Identity\IdentityProviderInterface'));

        $factory = new RoleServiceFactory();
        $roleService = $factory->createService($serviceManager);

        $this->assertInstanceOf('LmcRbac\Service\RoleService', $roleService);
        $this->assertEquals('guest', $roleService->getGuestRole());
        $this->assertAttributeSame($traversalStrategy, 'traversalStrategy', $roleService);
    }

    public function testIfRoleArrayPointerBeyondArrayEnd()
    {
        $options = new ModuleOptions([
            'identity_provider'    => 'LmcRbac\Identity\AuthenticationProvider',
            'guest_role'           => 'guest',
            'role_provider'        => [
                'LmcRbac\Role\InMemoryRoleProvider' => [
                    'foo'
                ]
            ]
        ]);

        // Simulate if array pointer beyond end of array. E.g after 'while(next($roleProvider)) { //do }'
        $roleProvider = $options->getRoleProvider();
        next($roleProvider);
        $options->setRoleProvider($roleProvider);

        $traversalStrategy = $this->getMock('Rbac\Traversal\Strategy\TraversalStrategyInterface');
        $roleProvider = $this->getMock('\LmcRbac\Role\RoleProviderInterface');

        $rbac = $this
            ->getMockBuilder('Rbac\Rbac')
            ->disableOriginalConstructor()
            ->getMock();
        $rbac->expects($this->once())
            ->method('getTraversalStrategy')
            ->will($this->returnValue(
                $traversalStrategy
            ));

        $pluginManager = $this
            ->getMockBuilder('\LmcRbac\Role\RoleProviderPluginManager')
            ->disableOriginalConstructor()
            ->getMock();
        $pluginManager->expects($this->once())
            ->method('get')
            ->with('LmcRbac\Role\InMemoryRoleProvider', ['foo'])
            ->will($this->returnValue(
                $roleProvider
            ));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('LmcRbac\Options\ModuleOptions', $options);
        $serviceManager->setService('Rbac\Rbac', $rbac);
        $serviceManager->setService('LmcRbac\Role\RoleProviderPluginManager', $pluginManager);
        $serviceManager->setService('LmcRbac\Identity\AuthenticationProvider', $this->getMock('LmcRbac\Identity\IdentityProviderInterface'));

        $factory = new RoleServiceFactory();
        $factory->createService($serviceManager);
    }

    public function testThrowExceptionIfNoRoleProvider()
    {
        $this->setExpectedException('LmcRbac\Exception\RuntimeException');

        $options = new ModuleOptions([
            'identity_provider' => 'LmcRbac\Identity\AuthenticationProvider',
            'guest_role'        => 'guest',
            'role_provider'     => []
        ]);

        $serviceManager = new ServiceManager();
        $serviceManager->setService('LmcRbac\Options\ModuleOptions', $options);
        $serviceManager->setService(
            'LmcRbac\Identity\AuthenticationProvider',
            $this->getMock('LmcRbac\Identity\IdentityProviderInterface')
        );

        $factory     = new RoleServiceFactory();
        $factory->createService($serviceManager);
    }
}