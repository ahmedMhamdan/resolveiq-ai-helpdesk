<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class AutoTranslationService
{
    private const SUPPORTED_LOCALES = ['en', 'ar'];

    public function translateMany(array $texts, string $targetLocale): array
    {
        $targetLocale = strtolower($targetLocale);

        if (! in_array($targetLocale, self::SUPPORTED_LOCALES, true)) {
            return [];
        }

        $texts = collect($texts)
            ->filter(fn ($text) => is_string($text) && trim($text) !== '')
            ->map(fn ($text) => trim($text))
            ->unique()
            ->values()
            ->all();

        if ($targetLocale === 'en') {
            return collect($texts)
                ->mapWithKeys(fn ($text) => [$text => $text])
                ->all();
        }

        $translations = [];
        $missing = [];

        foreach ($texts as $text) {
            $glossaryTranslation = $this->glossaryTranslation($text, $targetLocale);

            if ($glossaryTranslation !== null) {
                $translations[$text] = $glossaryTranslation;
                continue;
            }

            $cacheKey = $this->cacheKey($text, $targetLocale);
            $cached = Cache::get($cacheKey);

            if ($cached && $cached !== $text) {
                $translations[$text] = $cached;
            } else {
                $missing[] = $text;
            }
        }

        if (! empty($missing)) {
            $freshTranslations = $this->translateWithOpenRouter($missing, $targetLocale);

            foreach ($missing as $index => $text) {
                $translated = trim($freshTranslations[$index] ?? '');

                if ($translated === '') {
                    $translated = $text;
                }

                $translations[$text] = $translated;

                if ($translated !== $text) {
                    Cache::put($this->cacheKey($text, $targetLocale), $translated, now()->addDays(30));
                }
            }
        }

        return $translations;
    }

    private function glossaryTranslation(string $text, string $targetLocale): ?string
    {
        if ($targetLocale !== 'ar') {
            return null;
        }

        $normalized = preg_replace('/\s+/', ' ', trim($text));

        $translations = [
            '+ New Ticket' => '+ تذكرة جديدة',
            '+ Ticket' => '+ تذكرة',
            'Account' => 'الحساب',
            'Active requests' => 'طلبات نشطة',
            'Agents' => 'الوكلاء',
            'AI Assistant' => 'المساعد الذكي',
            'AI Helpdesk' => 'مكتب الدعم الذكي',
            'AI Powered' => 'مدعوم بالذكاء الاصطناعي',
            'All Priority' => 'كل الأولويات',
            'All Status' => 'كل الحالات',
            'Assigned Tickets' => 'التذاكر المعينة',
            'Cancel' => 'إلغاء',
            'Create Ticket' => 'إنشاء تذكرة',
            'Created' => 'تاريخ الإنشاء',
            'Dashboard' => 'لوحة التحكم',
            'Department' => 'القسم',
            'Departments' => 'الأقسام',
            'Due Date' => 'تاريخ الاستحقاق',
            'Edit' => 'تعديل',
            'Filter' => 'تصفية',
            'High' => 'عالية',
            'Interface language switched to English.' => 'تم تغيير لغة الواجهة إلى الإنجليزية.',
            'Knowledge Base' => 'قاعدة المعرفة',
            'Latest Tickets' => 'أحدث التذاكر',
            'Logout' => 'تسجيل الخروج',
            'Needs attention' => 'تحتاج متابعة',
            'New support requests in the workspace.' => 'أحدث طلبات الدعم في مساحة العمل.',
            'Notifications' => 'الإشعارات',
            'No unread notifications.' => 'لا توجد إشعارات غير مقروءة.',
            'Open Tickets' => 'التذاكر المفتوحة',
            'Overview' => 'نظرة عامة',
            'Overview of support performance, ticket volume, and urgent issues.' => 'نظرة على أداء الدعم وحجم التذاكر والمشكلات العاجلة.',
            'Pending' => 'قيد الانتظار',
            'Priority' => 'الأولوية',
            'Profile' => 'الملف الشخصي',
            'Requester' => 'مقدم الطلب',
            'Resolved tickets' => 'تذاكر محلولة',
            'Resolve tickets faster with summaries, suggested replies, and support insights.' => 'حل التذاكر بسرعة أكبر باستخدام الملخصات والردود المقترحة ورؤى الدعم.',
            'Search' => 'بحث',
            'Search all tickets...' => 'ابحث في كل التذاكر...',
            'Search assigned tickets...' => 'ابحث في التذاكر المعينة...',
            'Search tickets...' => 'ابحث في التذاكر...',
            'Search your tickets...' => 'ابحث في تذاكرك...',
            'Solved' => 'تم الحل',
            'Status' => 'الحالة',
            'Ticket' => 'التذكرة',
            'Tickets' => 'التذاكر',
            'Unread' => 'غير مقروءة',
            'unread' => 'غير مقروءة',
            'Updated' => 'آخر تحديث',
            'Urgent' => 'عاجلة',
            'Users' => 'المستخدمون',
            'View All' => 'عرض الكل',
            'View all' => 'عرض الكل',
            'View Tickets' => 'عرض التذاكر',
            'Waiting for updates' => 'بانتظار التحديثات',
        ];

        $translations += [
            'Agent' => 'الوكيل',
            'All' => 'الكل',
            'Apply Due Date' => 'تطبيق تاريخ الاستحقاق',
            'Apply Priority' => 'تطبيق الأولوية',
            'Assigned Agent' => 'الوكيل المعين',
            'Attachments' => 'المرفقات',
            'Choose Image' => 'اختيار صورة',
            'Closed At' => 'تاريخ الإغلاق',
            'Content' => 'المحتوى',
            'Delete all' => 'حذف الكل',
            'Delete read' => 'حذف المقروء',
            'Draft' => 'مسودة',
            'First Response' => 'أول رد',
            'Internal note' => 'ملاحظة داخلية',
            'Knowledge sources used' => 'مصادر المعرفة المستخدمة',
            'Mark all as read' => 'تحديد الكل كمقروء',
            'Name' => 'الاسم',
            'New' => 'جديد',
            'No activity yet.' => 'لا يوجد نشاط بعد.',
            'No content returned.' => 'لم يتم إرجاع محتوى.',
            'No image selected' => 'لم يتم اختيار صورة',
            'No notifications found.' => 'لا توجد إشعارات.',
            'No replies yet.' => 'لا توجد ردود بعد.',
            'Not closed' => 'لم تغلق',
            'Not resolved' => 'لم يتم الحل',
            'Not yet' => 'ليس بعد',
            'Password' => 'كلمة المرور',
            'Published' => 'منشور',
            'Read' => 'مقروءة',
            'Reopen Ticket' => 'إعادة فتح التذكرة',
            'Role' => 'الدور',
            'Ticket Details' => 'تفاصيل التذكرة',
            'Ticket Title' => 'عنوان التذكرة',
            'Update Agent' => 'تحديث الوكيل',
            'Update Article' => 'تحديث المقال',
            'Update Department' => 'تحديث القسم',
            'Use as Internal Note' => 'استخدام كملاحظة داخلية',
            'Use as Reply' => 'استخدام كرد',
            'open' => 'مفتوحة',
            'pending' => 'قيد الانتظار',
            'solved' => 'تم الحل',
            'closed' => 'مغلقة',
            'low' => 'منخفضة',
            'medium' => 'متوسطة',
            'high' => 'عالية',
            'urgent' => 'عاجلة',
            'not set' => 'غير محددة',
            'Agent created successfully.' => 'تم إنشاء الوكيل بنجاح.',
            'Agent updated successfully.' => 'تم تحديث الوكيل بنجاح.',
            'Article created successfully.' => 'تم إنشاء المقال بنجاح.',
            'Article updated successfully.' => 'تم تحديث المقال بنجاح.',
            'Department created successfully.' => 'تم إنشاء القسم بنجاح.',
            'Department updated successfully.' => 'تم تحديث القسم بنجاح.',
            'Notification deleted.' => 'تم حذف الإشعار.',
            'All notifications marked as read.' => 'تم تحديد كل الإشعارات كمقروءة.',
            'Profile updated successfully.' => 'تم تحديث الملف الشخصي بنجاح.',
            'Ticket created successfully.' => 'تم إنشاء التذكرة بنجاح.',
            'Ticket updated successfully.' => 'تم تحديث التذكرة بنجاح.',
            'Ticket assigned successfully.' => 'تم تعيين التذكرة بنجاح.',
            'Ticket reopened successfully.' => 'تمت إعادة فتح التذكرة بنجاح.',
            'Ticket closed successfully.' => 'تم إغلاق التذكرة بنجاح.',
            'Reply added successfully.' => 'تمت إضافة الرد بنجاح.',
            'Reply deleted successfully.' => 'تم حذف الرد بنجاح.',
            'AI suggestion generated successfully.' => 'تم إنشاء اقتراح الذكاء الاصطناعي بنجاح.',
            'AI suggested priority applied successfully.' => 'تم تطبيق أولوية الذكاء الاصطناعي المقترحة بنجاح.',
            'AI suggested due date applied successfully.' => 'تم تطبيق تاريخ الاستحقاق المقترح بنجاح.',
        ];

        return $translations[$normalized] ?? null;
    }

    private function translateWithOpenRouter(array $texts, string $targetLocale): array
    {
        $apiKey = config('services.openrouter.api_key')
            ?? config('services.openrouter.key')
            ?? env('OPENROUTER_API_KEY');

        if (! $apiKey) {
            return $texts;
        }

        $model = config('services.openrouter.model')
            ?? env('OPENROUTER_MODEL')
            ?? 'openai/gpt-4o-mini';

        $targetLanguage = $targetLocale === 'ar' ? 'Arabic' : 'English';

        try {
            $response = Http::timeout(25)
                ->retry(1, 400)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => config('app.name', 'ResolveIQ'),
                ])
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => implode("\n", [
                                'You are a professional UI localization engine for a Laravel helpdesk app named ResolveIQ.',
                                'Translate only the provided interface text into the requested language.',
                                'Keep product names, route names, IDs, emails, URLs, numbers, placeholders, and symbols unchanged.',
                                'Keep translations concise and suitable for buttons, labels, navigation, and dashboard UI.',
                                'Return valid JSON only in this exact shape: {"translations":["..."]}.',
                                'The translations array must have the same length and order as the input array.',
                            ]),
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode([
                                'target_language' => $targetLanguage,
                                'texts' => array_values($texts),
                            ], JSON_UNESCAPED_UNICODE),
                        ],
                    ],
                    'temperature' => 0.1,
                ]);

            if (! $response->successful()) {
                return $texts;
            }

            $content = data_get($response->json(), 'choices.0.message.content', '');
            $decoded = $this->decodeJsonResponse($content);

            $translations = $decoded['translations'] ?? [];

            if (! is_array($translations)) {
                return $texts;
            }

            return array_values($translations);
        } catch (Throwable $e) {
            report($e);

            return $texts;
        }
    }

    private function decodeJsonResponse(string $content): array
    {
        $content = trim($content);
        $decoded = json_decode($content, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');

        if ($start === false || $end === false || $end <= $start) {
            return [];
        }

        $json = substr($content, $start, $end - $start + 1);
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function cacheKey(string $text, string $targetLocale): string
    {
        return 'resolveiq_ui_translation:' . $targetLocale . ':' . sha1($text);
    }
}
