<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin → Site settings (PRD §20/§22, /admin/settings in §31). The form is
 * generated from App\Support\SiteSettings; gated by 'manage-settings'.
 */
class SettingsController extends Controller
{
    public function edit(): View
    {
        $values = [];
        foreach (array_keys(SiteSettings::fields()) as $key) {
            $values[$key] = Setting::get($key);
        }

        return view('admin.settings.edit', [
            'groups' => SiteSettings::groups(),
            'values' => $values,
        ]);
    }

    public function update(Request $request, AuditLogger $audit): RedirectResponse
    {
        $fields = SiteSettings::fields();

        // Form names use "__" for "." so nested keys survive PHP's input parsing.
        $rules = [];
        foreach ($fields as $key => $field) {
            $rules[self::inputName($key)] = $field['rules'];
        }
        $rules[self::inputName('home.stats').'.*.value'] = ['required_with:'.self::inputName('home.stats').'.*.label', 'nullable', 'string', 'max:20'];
        $rules[self::inputName('home.stats').'.*.label'] = ['required_with:'.self::inputName('home.stats').'.*.value', 'nullable', 'string', 'max:60'];
        $rules[self::inputName('home.stats').'.*.color'] = ['nullable', Rule::in(array_keys(SiteSettings::STAT_COLOURS))];

        $validated = $request->validate($rules, [
            '*.starts_with' => 'Paste the embed address from Google Maps (it starts with https://www.google.com/maps/embed).',
            '*.regex' => 'Choose an image from the media library.',
        ], collect($fields)->mapWithKeys(fn ($f, $key) => [self::inputName($key) => strtolower($f['label'])])->all());

        $changed = [];

        DB::transaction(function () use ($fields, $validated, &$changed) {
            foreach ($fields as $key => $field) {
                $value = $validated[self::inputName($key)] ?? null;

                if ($field['type'] === 'stats') {
                    $rows = collect($value ?? [])
                        ->filter(fn ($row) => filled($row['value'] ?? null) && filled($row['label'] ?? null))
                        ->map(fn ($row) => ['value' => $row['value'], 'label' => $row['label'], 'color' => $row['color'] ?? 'blue'])
                        ->values()
                        ->all();
                    $stored = json_encode($rows, JSON_UNESCAPED_UNICODE);
                    $type = 'json';
                } else {
                    $stored = $value === null ? null : trim((string) $value);
                    $type = $field['type'] === 'textarea' ? 'textarea' : ($field['type'] === 'image' ? 'image' : 'text');
                }

                $setting = Setting::firstOrNew(['key' => $key]);
                $group = explode('.', $key)[0];

                if ($setting->value !== $stored || ! $setting->exists) {
                    $changed[] = $key;
                }

                $setting->fill(['group' => $group, 'type' => $type, 'value' => $stored])->save();
            }
        });

        Cache::forget('moaum.settings.types');

        if ($changed !== []) {
            $audit->log(
                action: 'settings.updated',
                description: 'Site settings updated: '.implode(', ', $changed),
                properties: ['keys' => $changed],
            );
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Settings saved.');
    }

    public static function inputName(string $key): string
    {
        return str_replace('.', '__', $key);
    }
}
