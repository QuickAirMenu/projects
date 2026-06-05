<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Air Menu Workspace Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all custom configurations for Air Menu Workspace
    | Project Management System
    |
    */

    'name' => env('APP_NAME', 'Air Menu | Workspace'),
    
    'url' => env('APP_URL', 'https://workspace.airmenu.net'),
    
    'company' => [
        'name' => 'Digital Business Solutions',
        'name_ar' => 'حلول الأعمال الرقمية',
        'email' => 'info@airmenu.net',
        'phone' => '+966 XX XXX XXXX',
        'address' => 'Jeddah, Saudi Arabia',
        'address_ar' => 'جدة، المملكة العربية السعودية',
        'tax_number' => '300000000000003',
    ],

    'timezone' => env('WORKSPACE_TIMEZONE', 'Asia/Riyadh'),
    
    'default_language' => env('WORKSPACE_DEFAULT_LANGUAGE', 'ar'),
    
    'supported_locales' => ['ar', 'en'],
    
    'currency' => [
        'code' => env('WORKSPACE_CURRENCY', 'SAR'),
        'symbol' => 'ر.س',
        'symbol_en' => 'SAR',
    ],
    
    'tax' => [
        'rate' => env('WORKSPACE_TAX_RATE', 15),
        'enabled' => true,
    ],
    
    'date_format' => env('WORKSPACE_DATE_FORMAT', 'Y-m-d'),
    
    'time_format' => env('WORKSPACE_TIME_FORMAT', 'H:i'),
    
    'datetime_format' => env('WORKSPACE_DATE_FORMAT', 'Y-m-d') . ' ' . env('WORKSPACE_TIME_FORMAT', 'H:i'),
    
    'pagination' => [
        'per_page' => 15,
        'per_page_options' => [10, 15, 25, 50, 100],
    ],
    
    'uploads' => [
        'max_file_size' => 10240, // KB (10MB)
        'allowed_extensions' => [
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'svg'],
            'archives' => ['zip', 'rar', '7z'],
        ],
        'paths' => [
            'projects' => 'uploads/projects',
            'tasks' => 'uploads/tasks',
            'clients' => 'uploads/clients',
            'contracts' => 'uploads/contracts',
            'invoices' => 'uploads/invoices',
            'avatars' => 'uploads/avatars',
        ],
    ],
    
    'project' => [
        'number_prefix' => 'PRJ',
        'default_priority' => 'medium',
        'workflow_steps' => [
            'استقبال الطلب',
            'تحليل الاحتياج',
            'التسعير والاعتماد',
            'تهيئة المشروع',
            'Kickoff',
            'التنفيذ',
            'المراجعة الداخلية',
            'إرسال للعميل',
            'التعديلات',
            'التسليم النهائي',
            'الإغلاق',
            'تقييم ما بعد المشروع',
        ],
    ],
    
    'task' => [
        'default_priority' => 'medium',
        'overdue_notification_days' => 1,
    ],
    
    'quote' => [
        'number_prefix' => 'QT',
        'valid_days' => 30,
    ],
    
    'invoice' => [
        'number_prefix' => 'INV',
        'due_days' => 30,
    ],
    
    'payment' => [
        'number_prefix' => 'PAY',
        'methods' => [
            'bank_transfer' => 'تحويل بنكي',
            'cash' => 'نقدي',
            'cheque' => 'شيك',
            'card' => 'بطاقة',
            'online' => 'دفع إلكتروني',
            'other' => 'أخرى',
        ],
    ],

    'tinify' => [
        'api_key' => env('TINIFY_API_KEY'),
    ],
    
    'contract' => [
        'number_prefix' => 'CNT',
    ],
    
    'change_request' => [
        'number_prefix' => 'CR',
    ],
    
    'notifications' => [
        'channels' => ['database', 'mail'],
        'enabled_types' => [
            'task_assigned',
            'task_deadline_approaching',
            'task_overdue',
            'project_status_changed',
            'comment_added',
            'invoice_created',
            'payment_received',
            'quote_sent',
        ],
    ],
    
    'dashboard' => [
        'recent_projects_count' => 5,
        'recent_tasks_count' => 10,
        'deadline_alert_days' => 3,
    ],
    
    'activity_log' => [
        'enabled' => true,
        'log_name' => 'default',
    ],
    
];
