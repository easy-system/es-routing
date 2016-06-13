Configuration
=============

The configuration of the router should be represented in the system 
configuration with `router` key.

Two subsections, `defaults` and `routes`,  specifies the configuration of the 
router defaults and counfiguration of routes respectively:
```
$systemConfig = [
    'router' => [
        'defaults' => [], // defaults of router
        'routes'   => [], // the configuration of routes
    ],
];
```

# The configuration of route

## Basic route configuration

The required elements of route configuration are:

- The name of route
- The route path
- The full name of controller for this route, exactly as it was registered in 
  the system services. This name must be specified in the `defaults` section.
  
The name of the controller action can also be optionally specified in the 
`defaults` section.

```
$systemConfig = [
    'router' => [
        'routes'   => [
            'foo.bar' => [
                'path'     => '/foo/bar',
                'defaults' => [
                    'controller' => 'Foo.Bar.Controller.Index',
                    'action'     => 'index',
                ],
            ],
        ],
    ],
];
```

In example above:

- The name of route is specified as `foo.bar`
- The route path is specified as `/foo/bar`
- The controller is specified as `Foo.Bar.Controller.Index`
- The action is specified as `index`

In the `defaults` section can be further specified the default values for the
variables of the route, if any:

```
$systemConfig = [
    'router' => [
        'routes'   => [
            'foo' => [
                'path'     => '/foo/:bar',
                'defaults' => [
                    'controller' => 'Foo.Controller.Index',
                    'action'     => 'index',
                    'bar'        => 100,
                ],
            ],
        ],
    ],
];
```

## Expected URL schemes

You can also specify the expected URL schemes in the `schemes` section:
```
$systemConfig = [
    'router' => [
        'routes'   => [
            'foo' => [
                'path'     => '/foo',
                'schemes' => ['https', 'http'],
                'defaults' => [
                    'controller' => 'Foo.Controller.Index',
                    'action'     => 'index',
                ],
            ],
        ],
    ],
];
```
So, the route will be checked for matching of the URL schemes.

## Expected methods of request
You can also specify the expected methods of request in the `methods` section:
```
$systemConfig = [
    'router' => [
        'routes'   => [
            'foo' => [
                'path'     => '/foo',
                'methods' => ['GET', 'POST'],
                'defaults' => [
                    'controller' => 'Foo.Controller.Index',
                    'action'     => 'index',
                ],
            ],
        ],
    ],
];
```
So, the route will be checked for matching of the request methods.

## Constrains

Additionally, you can specify constraints for the variables of route in the 
`constrains` section, if any:

```
$systemConfig = [
    'router' => [
        'routes'   => [
            'foo' => [
                'path'     => '/foo/:bar',
                'defaults' => [
                    'controller' => 'Foo.Controller.Index',
                    'action'     => 'index',
                    'bar'        => 100,
                ],
                'constrains' => [
                    'bar' => '\d+',
                ],
            ],
        ],
    ],
];
```

Here there is one very important point that need of your attention.
If the variable is optional, you can not use the modifier `+` as it disrupt the
logic of route matching.

Here is an example incorrect use of constraints:
```
$systemConfig = [
    'router' => [
        'routes'   => [
            'foo' => [
                'path'     => '/foo/~:bar', // the variable "bar" is specified as optional
                'defaults' => [
                    'controller' => 'Foo.Controller.Index',
                    'action'     => 'index',
                    'bar'        => 100,
                ],
                'constrains' => [
                    'bar' => '\d+', // invalid "+" modifier for an optional variable
                ],
            ],
        ],
    ],
];
```
