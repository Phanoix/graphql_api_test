<?php
namespace GraphQL\Elgg\Type;
use GraphQL\Elgg\AppContext;
use GraphQL\Elgg\Data\User;
use GraphQL\Elgg\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

class UserType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'User',
            'description' => 'Our Users',
            'fields' => function() {
                return [
                    'guid' => Types::id(),
                    'email' => Types::email(),
                    'photo' => [
                        'type' => Types::image(),
                        'description' => 'User photo URL',
                        'args' => [
                            'size' => Types::nonNull(Types::imageSizeEnum()),
                        ]
                    ],
                    'username' => [
                        'type' => Types::string(),
                    ],
                    'displayname' => [
                        'type' => Types::string(),
                    ],
                    'lastaction' => Types::int(),
                    'fieldWithError' => [
                        'type' => Types::string(),
                        'resolve' => function() {
                            throw new \Exception("This is error field");
                        }
                    ]
                ];
            },
            'interfaces' => [
                Types::node()
            ],
            'resolveField' => function($value, $args, $context, ResolveInfo $info) {
                $method = 'resolve' . ucfirst($info->fieldName);
                if (method_exists($this, $method)) {
                    return $this->{$method}($value, $args, $context, $info);
                } else {
                    return $value->{$info->fieldName};
                }
            }
        ];
        parent::__construct($config);
    }
    public function resolvePhoto(User $user, $args)
    {
        return elgg_view_entity_icon( get_entity($user->guid), $args['size'] );
    }
    public function resolveLastAction(User $user)
    {
        return get_entity($user->guid)->prev_last_action;
    }
}