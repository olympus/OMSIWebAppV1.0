<?php

return [
    
    // Service configurations.

    'services'          => [
        
        'demo'              => [
            'name'              => 'Demo',
            'class'             => 'Viewflex\Zoap\Demo\DemoService',
            'exceptions'        => [
                'Exception'
            ],
            'types'             => [
                'keyValue'          => 'Viewflex\Zoap\Demo\Types\KeyValue',
                'product'           => 'Viewflex\Zoap\Demo\Types\Product'
            ],
            'strategy'          => 'ArrayOfTypeComplex',
            'headers'           => [
                'Cache-Control'     => 'no-cache, no-store'
            ],
            'options'           => []
        ],

        'sappush'              => [
            'name'              => 'Sapimport',
            'class'             => 'Viewflex\Zoap\Sapimport\SapimportService',
            'exceptions'        => [
                'Exception'
            ],
            'types'             => [
                'keyValue'          => 'Viewflex\Zoap\Sapimport\Types\KeyValue',
                'CVMRequest'        => 'Viewflex\Zoap\Sapimport\Types\CVMRequest'
            ],
            'strategy'          => 'ArrayOfTypeSequence',
            'headers'           => [
                'Cache-Control'     => 'no-cache, no-store'
            ],
            'options'           => []
        ],

        'sappush_ArrayOfTypeSequence'              => [
            'name'              => 'Sapimport',
            'class'             => 'Viewflex\Zoap\Sapimport\SapimportService',
            'exceptions'        => [
                'Exception'
            ],
            'types'             => [
                'keyValue'          => 'Viewflex\Zoap\Sapimport\Types\KeyValue',
                'CVMRequest'        => 'Viewflex\Zoap\Sapimport\Types\CVMRequest'
            ],
            'strategy'          => 'ArrayOfTypeSequence',
            'headers'           => [
                'Cache-Control'     => 'no-cache, no-store'
            ],
            'options'           => []
        ],

        'sappush_ArrayOfTypeComplex'              => [
            'name'              => 'Sapimport',
            'class'             => 'Viewflex\Zoap\Sapimport\SapimportService1',
            'exceptions'        => [
                'Exception'
            ],
            'types'             => [
                'keyValue'          => 'Viewflex\Zoap\Sapimport\Types\KeyValue',
                'CVMRequest'        => 'Viewflex\Zoap\Sapimport\Types\CVMRequest'
            ],
            'strategy'          => 'ArrayOfTypeComplex',
            'headers'           => [
                'Cache-Control'     => 'no-cache, no-store'
            ],
            'options'           => []
        ],

        'sappush_DefaultComplexType'              => [
            'name'              => 'Sapimport',
            'class'             => 'Viewflex\Zoap\Sapimport\SapimportService1',
            'exceptions'        => [
                'Exception'
            ],
            'types'             => [
                'keyValue'          => 'Viewflex\Zoap\Sapimport\Types\KeyValue',
                'CVMRequest'        => 'Viewflex\Zoap\Sapimport\Types\CVMRequest'
            ],
            'strategy'          => 'DefaultComplexType',
            'headers'           => [
                'Cache-Control'     => 'no-cache, no-store'
            ],
            'options'           => []
        ],
        
    ],

    
    // Log exception trace stack?

    'logging'       => true,

    
    // Mock credentials for demo.

    'mock'          => [
        'user'              => 'test@test.com',
        //'password'          => 'tester',
        'token'             => 'tGSGYv8al1Ce6Rui8oa4Kjo8ADhYvR9x8KFZOeEGWgU1iscF7N2tUnI3t9bX'
    ],

    
];
