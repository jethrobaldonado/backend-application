<?php

namespace App\Http\Controllers\Api\v1;

use Auth;
use Filter;
use App\Models\Role;
use App\Models\Rule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

class RolesController extends ItemController
{
    /**
     * @return string
     */
    public function getItemClass(): string
    {
        return Role::class;
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'name' => 'required',
        ];
    }

    /**
     * @return string
     */
    public function getEventUniqueNamePart(): string
    {
        return 'role';
    }

    /**
     * @apiDefine RolesRelations
     *
     * @apiParam {String} [with]               For add relation model in response
     * @apiParam {Object} [users] `QueryParam` Roles's relation users. All params in <a href="#api-User-GetUserList" >@User</a>
     * @apiParam {Object} [rules] `QueryParam` Roles's relation rules. All params in <a href="#api-Rule-GetRulesActions" >@Rules</a>
     */

    /**
     * @apiDefine RolesRelationsExample
     * @apiParamExample {json} Request-With-Relations-Example:
     *  {
     *      "with":               "users,rules,users.tasks",
     *      "users.tasks.id":     [">", 1],
     *      "users.tasks.active": 1,
     *      "users.full_name":    ["like", "%lorem%"]
     *  }
     */

    /**
     * @api {post} /api/v1/roles/list List
     * @apiDescription Get list of Roles
     * @apiVersion 0.1.0
     * @apiName GetRolesList
     * @apiGroup Roles
     *
     * @apiParam {Integer}  [id]          `QueryParam` Role ID
     * @apiParam {Integer}  [user_id]     `QueryParam` Role's Users ID
     * @apiParam {String}   [name]        `QueryParam` Role Name
     * @apiParam {DateTime} [created_at]  `QueryParam` Role Creation DateTime
     * @apiParam {DateTime} [updated_at]  `QueryParam` Last Role update DataTime
     * @apiUse RolesRelations
     *
     * @apiParamExample {json} Simple-Request-Example:
     *  {
     *      "id":          [">", 1]
     *      "user_id":     ["=", [1,2,3]],
     *      "name":        ["like", "%lorem%"],
     *      "created_at":  [">", "2019-01-01 00:00:00"],
     *      "updated_at":  ["<", "2019-01-01 00:00:00"]
     *  }
     * @apiUse RolesRelationsExample
     *
     * @apiSuccess {Object[]} RoleList                  Roles (Array of objects)
     * @apiSuccess {Object}   RoleList.Role             Role object
     * @apiSuccess {Integer}  RoleList.Role.id          Role's ID
     * @apiSuccess {String}   RoleList.Role.name        Role's name
     * @apiSuccess {DateTime} RoleList.Role.created_at  Role's date time of create
     * @apiSuccess {DateTime} RoleList.Role.updated_at  Role's date time of update
     * @apiSuccess {DateTime} RoleList.Role.deleted_at  Role's date time of delete
     * @apiSuccess {Object[]} RoleList.Role.users       Role's User (Array of objects)
     * @apiSuccess {Object[]} RoleList.Role.rules       Role's Task (Array of objects)
     *
     * @apiUse NotLoggedIn
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $cls = $this->getItemClass();
        $cls::updateRules();

        if ($request->get('user_id')) {
            $request->offsetSet('users.id', $request->get('user_id'));
            $request->offsetUnset('user_id');
        }

        return parent::index($request);
    }

    /**
     * @api {post} /api/v1/roles/create Create
     * @apiDescription Create Role
     * @apiVersion 0.1.0
     * @apiName CreateRole
     * @apiGroup Role
     *
     * @apiParam {String} name Roles's name
     *
     * @apiParamExample {json} Simple-Request-Example:
     *  {
     *      "name": "test"
     *  }
     *
     * @apiSuccess {Object}   res             Response object
     * @apiSuccess {Integer}  res.id          Role's ID
     * @apiSuccess {String}   res.name        Role's name
     * @apiSuccess {DateTime} res.created_at  Role's date time of create
     * @apiSuccess {DateTime} res.updated_at  Role's date time of update
     *
     * @apiUse DefaultCreateErrorResponse
     *
     * @param Request $request
     *
     * @return JsonResponse
     */

    /**
     * @api {post} /api/v1/roles/show Show
     * @apiDescription Get Role Entity
     * @apiVersion 0.1.0
     * @apiName ShowRole
     * @apiGroup Role
     *
     * @apiParam {Integer}    id                        Role ID
     * @apiParam {String}     [name]       `QueryParam` Role Name
     * @apiParam {String}     [created_at] `QueryParam` Role date time of create
     * @apiParam {String}     [updated_at] `QueryParam` Role date time of update
     *
     * @apiUse RolesRelations
     *
     * @apiParamExample {json} Simple-Request-Example:
     *  {
     *      "id":          1,
     *      "name":        ["like", "%lorem%"],
     *      "description": ["like", "%lorem%"],
     *      "created_at":  [">", "2019-01-01 00:00:00"],
     *      "updated_at":  ["<", "2019-01-01 00:00:00"]
     *  }
     *
     * @apiUse RolesRelationsExample
     *
     * @apiSuccess {Object}   Role             Role object
     * @apiSuccess {Integer}  Role.id          Role ID
     * @apiSuccess {String}   Role.name        Role name
     * @apiSuccess {String}   Role.created_at  Role date time of create
     * @apiSuccess {String}   Role.updated_at  Role date time of update
     * @apiSuccess {String}   Role.deleted_at  Role date time of delete
     * @apiSuccess {Object[]} Role.users       Role User (Array of entities)
     * @apiSuccess {Object[]} Role.rules       Role Task (Array of entities)
     *
     * @apiSuccessExample {json} Answer Relations Example:
     * {
     *   "id": 1,
     *   "name": "root",
     *   "deleted_at": null,
     *   "created_at": "2018-09-25 06:15:07",
     *   "updated_at": "2018-09-25 06:15:07"
     * }
     *
     * @apiUse DefaultShowErrorResponse
     * @apiUse NotLoggedIn
     *
     * @param Request $request
     *
     * @return JsonResponse
     */

    /**
     * @api {post} /api/v1/roles/edit Edit
     * @apiDescription Edit Role
     * @apiVersion 0.1.0
     * @apiName EditRole
     * @apiGroup Role
     *
     * @apiParam {Integer} id   Role ID
     * @apiParam {String}  name Role Name
     *
     * @apiParamExample {json} Simple-Request-Example:
     *  {
     *      "id": 1,
     *      "name": "test"
     *  }
     *
     * @apiSuccess {Object}   Role            Role object
     * @apiSuccess {Integer}  Role.id         Role's ID
     * @apiSuccess {String}   Role.name       Role's name
     * @apiSuccess {DateTime} Role.created_at Role's date time of create
     * @apiSuccess {DateTime} Role.updated_at Role's date time of update
     * @apiSuccess {DateTime} Role.deleted_at Role's date time of delete
     *
     * @apiUse DefaultEditErrorResponse
     * @apiUse NotLoggedIn
     *
     * @param Request $request
     *
     * @return JsonResponse
     */

    /**
     * @api {post} /api/v1/roles/destroy Destroy
     * @apiUse DefaultDestroyRequestExample
     * @apiDescription Destroy Role
     * @apiVersion 0.1.0
     * @apiName DestroyRole
     * @apiGroup Role
     *
     * @apiParam {Integer} id Role ID
     *
     * @apiParamExample {json} Simple-Request-Example:
     *  {
     *      "id": 1
     *  }
     *
     * @apiUse DefaultDestroyResponse
     *
     * @param Request $request
     *
     * @return JsonResponse
     */

    /**
     * @api {post} /api/v1/roles/allowed-rules AllowedRules
     * @apiDescription Get Rule's allowed action list
     * @apiVersion 0.1.0
     * @apiName GetRulesAllowedActionList
     * @apiGroup Roles
     *
     * @apiParam {Integer} id Role's ID
     *
     * @apiParamExample {json} Simple-Request-Example:
     *  {
     *      "id": 1
     *  }
     *
     * @apiSuccess {Object[]} array               Array of Rule objects
     * @apiSuccess {Object}   array.object        Rule object
     * @apiSuccess {String}   array.object.object Object of rule
     * @apiSuccess {String}   array.object.action Action of rule
     * @apiSuccess {String}   array.object.name   Name of rule
     *
     * @apiSuccessExample {json} Answer Example:
     * [
     *   {
     *     "object": "attached-users",
     *     "action": "bulk-create",
     *     "name": "Attached User relation multiple create"
     *   },
     *   {
     *     "object": "attached-users",
     *     "action": "bulk-remove",
     *     "name": "Attached User relation multiple remove"
     *   }
     * ]
     *
     * @apiError (Error 400) {String} error  Name of error
     * @apiError (Error 400) {String} reason Reason of error
     *
     * @apiUse NotLoggedIn
     *
     * @apiErrorExample {json} Invalid ID Example:
     * {
     *   "error": "Validation fail",
     *   "reason": "Invalid id"
     * }
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function allowedRules(Request $request): JsonResponse
    {
        $roleId = Filter::process($this->getEventUniqueName('request.item.allowed-rules'), $request->get('id'));
        $isInt = is_int($roleId);
        $items = [];
        /** @var array[] $actionList */
        $actionList = Rule::getActionList();

        if ($roleId <= 0 || !$isInt) {
            return response()->json(Filter::process(
                $this->getEventUniqueName('answer.error.item.allowed-rules'),
                [
                    'error' => 'Validation fail',
                    'reason' => 'Invalid id',
                ]),
                400
            );
        }

        /** @var Builder $itemsQuery */
        $itemsQuery = Filter::process(
            $this->getEventUniqueName('answer.success.item.query.prepare'),
            $this->getQuery()
        );
        $role = $itemsQuery->find($roleId);

        if (!$role) {
            return response()->json(Filter::process(
                $this->getEventUniqueName('answer.error.item.allowed-rules'),
                [
                    'error' => 'Role not found',
                    'reason' => 'Invalid Id'
                ]),
                400
            );
        }

        /** @var Rule[] $rules */
        $rules = $role->rules;

        foreach ($rules as $rule) {
            if (!$rule->allow) {
                continue;
            }

            $items[] = [
                'object' => $rule->object,
                'action' => $rule->action,
                'name'   => $actionList[$rule->object][$rule->action]
            ];
        }

        return response()->json(Filter::process(
            $this->getEventUniqueName('answer.success.item.allowed-rules'),
            $items
        ));
    }

    /**
     * @param bool $withRelations
     *
     * @return Builder
     */
    protected function getQuery($withRelations = true): Builder
    {
        $query = parent::getQuery($withRelations);
        $full_access = Role::can(Auth::user(), 'roles', 'full_access');
        $relations_access = Role::can(Auth::user(), 'users', 'relations');

        if ($full_access) {
            return $query;
        }

        $user_role_id = collect(Auth::user()->role_id);

        if ($relations_access) {
            $attached_users_role_id = collect(Auth::user()->attached_users)->flatMap(function($user) {
                return collect($user->role_id);
            });
            $roles_id = collect([$user_role_id, $attached_users_role_id])->collapse()->unique();
            $query->whereIn('id', $roles_id);
        } else {
            $query->whereIn('id', $user_role_id);
        }

        return $query;
    }
}
