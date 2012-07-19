<?php
/**
 * File containing the Role controller class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Controller;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Message;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\RoleService;
use eZ\Publish\API\Repository\Values\User\RoleCreateStruct;
use eZ\Publish\API\Repository\Values\User\RoleUpdateStruct;

use Qafoo\RMF;

/**
 * Role controller
 */
class Role
{
    /**
     * Input dispatcher
     *
     * @var \eZ\Publish\Core\REST\Common\Input\Dispatcher
     */
    protected $inputDispatcher;

    /**
     * URL handler
     *
     * @var \eZ\Publish\Core\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * Role service
     *
     * @var \eZ\Publish\API\Repository\RoleService
     */
    protected $roleService;

    /**
     * Construct controller
     *
     * @param \eZ\Publish\Core\REST\Common\Input\Dispatcher $inputDispatcher
     * @param \eZ\Publish\Core\REST\Common\UrlHandler $urlHandler
     * @param \eZ\Publish\API\Repository\RoleService $roleService
     */
    public function __construct( Input\Dispatcher $inputDispatcher, UrlHandler $urlHandler, RoleService $roleService )
    {
        $this->inputDispatcher = $inputDispatcher;
        $this->urlHandler      = $urlHandler;
        $this->roleService     = $roleService;
    }

    /**
     * Create new role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\CreatedRole
     */
    public function createRole( RMF\Request $request )
    {
        return new Values\CreatedRole( array(
            'role' => $this->roleService->createRole(
                $this->inputDispatcher->parse( new Message(
                    array( 'Content-Type' => $request->contentType ),
                    $request->body
                ) )
            ) )
        );
    }

    /**
     * Load list of roles
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RoleList
     */
    public function listRoles( RMF\Request $request )
    {
        return new Values\RoleList(
            $this->roleService->loadRoles()
        );
    }

    /**
     * Load role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function loadRole( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'role', $request->path );
        return $this->roleService->loadRole( $values['role'] );
    }

    /**
     * Load role by identifier
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\RoleList
     */
    public function loadRoleByIdentifier( RMF\Request $request )
    {
        return new Values\RoleList(
            array(
                $this->roleService->loadRoleByIdentifier( $request->variables['identifier'] )
            )
        );
    }

    /**
     * Updates a role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\Role
     */
    public function updateRole( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'role', $request->path );
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );
        return $this->roleService->updateRole(
            $this->roleService->loadRole( $values['role'] ),
            $this->mapToUpdateStruct( $createStruct )
        );
    }

    /**
     * Delete a role by ID
     *
     * @param RMF\Request $request
     */
    public function deleteRole( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'role', $request->path );
        return $this->roleService->deleteRole(
            $this->roleService->loadRole( $values['role'] )
        );
    }

    /**
     * Loads the policies for the role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\Core\REST\Server\Values\PolicyList
     */
    public function loadPolicies( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policies', $request->path );

        $loadedRole = $this->roleService->loadRole( $values['role'] );

        return new Values\PolicyList( $loadedRole->id, $loadedRole->getPolicies() );
    }

    /**
     * Deletes all policies from a role
     *
     * @param RMF\Request $request
     */
    public function deletePolicies( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policies', $request->path );

        $loadedRole = $this->roleService->loadRole( $values['role'] );

        foreach ( $loadedRole->getPolicies() as $rolePolicy )
        {
            $this->roleService->removePolicy( $loadedRole, $rolePolicy );
        }
    }

    /**
     * Adds a policy to role
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function addPolicy( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policies', $request->path );
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );

        $role = $this->roleService->addPolicy(
            $this->roleService->loadRole( $values['role'] ),
            $createStruct
        );

        $policies = $role->getPolicies();

        $policyToReturn = $policies[0];
        for ( $i = 1, $count = count( $policies ); $i < $count; $i++ )
        {
            if ( $policies[$i]->id > $policyToReturn->id )
                $policyToReturn = $policies[$i];
        }

        return $policyToReturn;
    }

    /**
     * Updates a policy
     *
     * @param RMF\Request $request
     * @return \eZ\Publish\API\Repository\Values\User\Policy
     */
    public function updatePolicy( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policy', $request->path );
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                array( 'Content-Type' => $request->contentType ),
                $request->body
            )
        );

        $role = $this->roleService->loadRole( $values['role'] );
        foreach ( $role->getPolicies() as $policy )
        {
            if ( $policy->id == $values['policy'] )
            {
                return $this->roleService->updatePolicy(
                    $policy,
                    $updateStruct
                );
            }
        }

        return null;
    }

    /**
     * Delete a policy from role
     *
     * @param RMF\Request $request
     */
    public function deletePolicy( RMF\Request $request )
    {
        $values = $this->urlHandler->parse( 'policy', $request->path );

        $role = $this->roleService->loadRole( $values['role'] );

        $policy = null;
        foreach ( $role->getPolicies() as $rolePolicy )
        {
            if ( $rolePolicy->id == $values['policy'] )
            {
                $policy = $rolePolicy;
                break;
            }
        }

        if ( $policy !== null )
            $this->roleService->removePolicy( $role, $policy );
    }

    /**
     * Maps a RoleCreateStruct to a RoleUpdateStruct.
     *
     * Needed since both structs are encoded into the same media type on input.
     *
     * @param \eZ\Publish\API\Repository\Values\User\RoleCreateStruct $createStruct
     * @return \eZ\Publish\API\Repository\Values\User\RoleUpdateStruct
     */
    protected function mapToUpdateStruct( RoleCreateStruct $createStruct )
    {
        return new RoleUpdateStruct(
            array(
                'identifier' => $createStruct->identifier
            )
        );
    }
}
