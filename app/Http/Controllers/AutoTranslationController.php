<?php

namespace App\Http\Controllers;

use App\Services\AutoTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AutoTranslationController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['en', 'ar'], true), 404);

        session(['locale' => $locale]);

        if ($request->user()) {
            $request->user()->forceFill([
                'locale' => $locale,
            ])->save();
        }

        return back()->with(
            'success',
            $locale === 'ar'
                ? 'تم تغيير لغة الواجهة إلى العربية.'
                : 'Interface language switched to English.'
        );
    }

    public function translate(Request $request, AutoTranslationService $translator): JsonResponse
    {
        $data = $request->validate([
            'locale' => ['required', Rule::in(['en', 'ar'])],
            'texts' => ['required', 'array', 'max:180'],
            'texts.*' => ['required', 'string', 'max:500'],
        ]);

        return response()->json([
            'translations' => $translator->translateMany($data['texts'], $data['locale']),
        ]);
    }
}
