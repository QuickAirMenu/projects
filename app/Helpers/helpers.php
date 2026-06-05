<?php

if (!function_exists('workspace_config')) {
    /**
     * Get workspace configuration value
     */
    function workspace_config($key, $default = null)
    {
        if ($key === 'company_logo') {
            $logo = \App\Models\Setting::where('key', 'company_logo')->value('value') ?: config('workspace.company_logo', $default);

            if (blank($logo)) {
                return $default;
            }

            if (filter_var($logo, FILTER_VALIDATE_URL)) {
                return $logo;
            }

            return \Illuminate\Support\Facades\Storage::url($logo);
        }

        return config("workspace.{$key}", $default);
    }
}

if (!function_exists('pdf_logo_data')) {
    /**
     * Resolve the company logo into a PDF-safe image source.
     */
    function pdf_logo_data($logo = null)
    {
        if (blank($logo)) {
            $logo = \App\Models\Setting::where('key', 'company_logo')->value('value') ?: config('workspace.company_logo');
        }

        if (blank($logo)) {
            return null;
        }

        if (str_starts_with($logo, 'data:image/')) {
            return $logo;
        }

        if (filter_var($logo, FILTER_VALIDATE_URL)) {
            $content = @file_get_contents($logo);

            if ($content !== false) {
                $extension = strtolower(pathinfo(parse_url($logo, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                $mime = $extension === 'svg' ? 'image/svg+xml' : 'image/' . ($extension ?: 'png');

                return 'data:' . $mime . ';base64,' . base64_encode($content);
            }

            return $logo;
        }

        $paths = [
            public_path(ltrim($logo, '/')),
            storage_path('app/public/' . ltrim(preg_replace('#^storage/#', '', $logo), '/')),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = $extension === 'svg' ? 'image/svg+xml' : 'image/' . ($extension ?: 'png');

                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        return $logo;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format number as currency
     */
    function format_currency($amount, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $symbol = $locale === 'ar' ? workspace_config('currency.symbol') : workspace_config('currency.symbol_en');
        
        return number_format($amount, 2) . ' ' . $symbol;
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date according to workspace settings
     */
    function format_date($date, $format = null)
    {
        if (!$date) return '-';
        
        $format = $format ?? workspace_config('date_format');
        return \Carbon\Carbon::parse($date)->format($format);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime according to workspace settings
     */
    function format_datetime($datetime, $format = null)
    {
        if (!$datetime) return '-';
        
        $format = $format ?? workspace_config('datetime_format');
        return \Carbon\Carbon::parse($datetime)->format($format);
    }
}

if (!function_exists('human_date')) {
    /**
     * Get human-readable date
     */
    function human_date($date, $locale = null)
    {
        if (!$date) return '-';
        
        $locale = $locale ?? app()->getLocale();
        return \Carbon\Carbon::parse($date)->locale($locale)->diffForHumans();
    }
}

if (!function_exists('generate_number')) {
    /**
     * Generate unique number for entities (project, invoice, quote, etc.)
     */
    function generate_number($prefix, $lastNumber = 0)
    {
        $year = date('Y');
        $month = date('m');
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}{$month}-{$nextNumber}";
    }
}

if (!function_exists('status_badge')) {
    /**
     * Get status badge HTML
     */
    function status_badge($status, $type = 'primary')
    {
        $colors = [
            'primary' => 'bg-blue-100 text-blue-800',
            'success' => 'bg-green-100 text-green-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'danger' => 'bg-red-100 text-red-800',
            'info' => 'bg-cyan-100 text-cyan-800',
            'secondary' => 'bg-gray-100 text-gray-800',
        ];
        
        $color = $colors[$type] ?? $colors['primary'];
        
        return "<span class='px-2 py-1 text-xs font-semibold rounded-full {$color}'>{$status}</span>";
    }
}

if (!function_exists('priority_badge')) {
    /**
     * Get priority badge HTML
     */
    function priority_badge($priority)
    {
        $badges = [
            'low' => ['color' => 'bg-gray-100 text-gray-800', 'icon' => 'fa-arrow-down', 'text' => __('common.priority.low')],
            'medium' => ['color' => 'bg-blue-100 text-blue-800', 'icon' => 'fa-minus', 'text' => __('common.priority.medium')],
            'high' => ['color' => 'bg-orange-100 text-orange-800', 'icon' => 'fa-arrow-up', 'text' => __('common.priority.high')],
            'urgent' => ['color' => 'bg-red-100 text-red-800', 'icon' => 'fa-exclamation', 'text' => __('common.priority.urgent')],
        ];
        
        $badge = $badges[$priority] ?? $badges['medium'];
        
        return "<span class='inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full {$badge['color']}'>
                    <i class='fas {$badge['icon']} mr-1'></i>
                    {$badge['text']}
                </span>";
    }
}

if (!function_exists('payment_status_badge')) {
    /**
     * Get payment status badge
     */
    function payment_status_badge($status)
    {
        $badges = [
            'unpaid' => ['color' => 'danger', 'text' => __('finance.payment_status.unpaid')],
            'partially_paid' => ['color' => 'warning', 'text' => __('finance.payment_status.partially_paid')],
            'paid' => ['color' => 'success', 'text' => __('finance.payment_status.paid')],
            'overdue' => ['color' => 'danger', 'text' => __('finance.payment_status.overdue')],
        ];
        
        $badge = $badges[$status] ?? $badges['unpaid'];
        
        return status_badge($badge['text'], $badge['color']);
    }
}

if (!function_exists('file_icon')) {
    /**
     * Get file icon based on extension
     */
    function file_icon($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $icons = [
            'pdf' => 'fa-file-pdf text-red-500',
            'doc' => 'fa-file-word text-blue-500',
            'docx' => 'fa-file-word text-blue-500',
            'xls' => 'fa-file-excel text-green-500',
            'xlsx' => 'fa-file-excel text-green-500',
            'ppt' => 'fa-file-powerpoint text-orange-500',
            'pptx' => 'fa-file-powerpoint text-orange-500',
            'jpg' => 'fa-file-image text-purple-500',
            'jpeg' => 'fa-file-image text-purple-500',
            'png' => 'fa-file-image text-purple-500',
            'gif' => 'fa-file-image text-purple-500',
            'zip' => 'fa-file-archive text-gray-500',
            'rar' => 'fa-file-archive text-gray-500',
        ];
        
        return $icons[$extension] ?? 'fa-file text-gray-400';
    }
}

if (!function_exists('file_size_format')) {
    /**
     * Format file size in human-readable format
     */
    function file_size_format($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

if (!function_exists('calculate_progress')) {
    /**
     * Calculate progress percentage
     */
    function calculate_progress($completed, $total)
    {
        if ($total == 0) return 0;
        
        return round(($completed / $total) * 100);
    }
}

if (!function_exists('user_avatar')) {
    /**
     * Get user avatar URL or initials
     */
    function user_avatar($user, $size = 40)
    {
        if ($user->avatar) {
            return asset($user->avatar);
        }
        
        // Generate initials avatar
        $initials = strtoupper(substr($user->name, 0, 2));
        $colors = ['bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-purple-500', 'bg-pink-500'];
        $color = $colors[ord($user->name[0]) % count($colors)];
        
        return "https://ui-avatars.com/api/?name={$initials}&size={$size}&background=random";
    }
}

if (!function_exists('can_user')) {
    /**
     * Check if user has permission
     */
    function can_user($permission)
    {
        return auth()->check() && auth()->user()->can($permission);
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if user has role
     */
    function has_role($role)
    {
        return auth()->check() && auth()->user()->hasRole($role);
    }
}

if (!function_exists('activity_icon')) {
    /**
     * Get activity icon based on type
     */
    function activity_icon($type)
    {
        $icons = [
            'created' => 'fa-plus-circle text-green-500',
            'updated' => 'fa-edit text-blue-500',
            'deleted' => 'fa-trash text-red-500',
            'assigned' => 'fa-user-plus text-purple-500',
            'completed' => 'fa-check-circle text-green-500',
            'commented' => 'fa-comment text-blue-500',
            'uploaded' => 'fa-upload text-cyan-500',
        ];
        
        return $icons[$type] ?? 'fa-circle text-gray-400';
    }
}

if (!function_exists('is_rtl')) {
    /**
     * Check if current locale is RTL
     */
    function is_rtl()
    {
        return app()->getLocale() === 'ar';
    }
}

if (!function_exists('direction')) {
    /**
     * Get text direction based on locale
     */
    function direction()
    {
        return is_rtl() ? 'rtl' : 'ltr';
    }
}
