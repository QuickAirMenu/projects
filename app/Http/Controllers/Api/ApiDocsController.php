<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiDocsController extends Controller
{
    public function ui()
    {
        return view('api.docs');
    }

    public function spec()
    {
        return response()->json($this->buildSpec());
    }

    protected function buildSpec(): array
    {
        $list = function (string $summary, string $tag) {
            return [
                'get' => [
                    'tags' => [$tag],
                    'summary' => $summary,
                    'security' => [['bearerAuth' => []]],
                    'parameters' => [
                        ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'maximum' => 100]],
                    ],
                    'responses' => ['200' => ['description' => 'Paginated collection']],
                ],
            ];
        };

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'iProjects API v1',
                'version' => '1.0.0',
                'description' => 'REST API مع مصادقة Sanctum. أنشئ توكن من صفحة «توكنات API» وأرسله كـ Bearer.',
            ],
            'servers' => [['url' => url('/api/v1')]],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer'],
                ],
            ],
            'paths' => [
                '/auth/login' => [
                    'post' => [
                        'tags' => ['Auth'],
                        'summary' => 'تسجيل الدخول والحصول على توكن',
                        'requestBody' => [
                            'required' => true,
                            'content' => ['application/json' => ['schema' => [
                                'type' => 'object',
                                'required' => ['email', 'password'],
                                'properties' => [
                                    'email' => ['type' => 'string', 'format' => 'email'],
                                    'password' => ['type' => 'string'],
                                    'device_name' => ['type' => 'string'],
                                ],
                            ]]],
                        ],
                        'responses' => [
                            '200' => ['description' => 'token + user'],
                            '422' => ['description' => 'بيانات غير صحيحة'],
                        ],
                    ],
                ],
                '/auth/logout' => ['post' => ['tags' => ['Auth'], 'summary' => 'إلغاء التوكن الحالي', 'security' => [['bearerAuth' => []]], 'responses' => ['200' => ['description' => 'ok']]]],
                '/auth/logout-all' => ['post' => ['tags' => ['Auth'], 'summary' => 'إلغاء كل التوكنات', 'security' => [['bearerAuth' => []]], 'responses' => ['200' => ['description' => 'ok']]]],
                '/me' => ['get' => ['tags' => ['Auth'], 'summary' => 'بيانات المستخدم الحالي', 'security' => [['bearerAuth' => []]], 'responses' => ['200' => ['description' => 'user']]]],
                '/dashboard' => ['get' => ['tags' => ['Dashboard'], 'summary' => 'ملخص لوحة التحكم', 'security' => [['bearerAuth' => []]], 'responses' => ['200' => ['description' => 'KPIs']]]],
                '/projects' => $list('قائمة المشاريع', 'Projects'),
                '/clients' => $list('قائمة العملاء', 'Clients'),
                '/invoices' => $list('قائمة الفواتير', 'Invoices'),
                '/quotes' => $list('قائمة عروض الأسعار', 'Quotes'),
                '/tasks' => $list('قائمة المهام', 'Tasks'),
            ],
        ];
    }
}
