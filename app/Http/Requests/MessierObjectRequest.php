<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\MessierObject;
use Closure;
use Illuminate\Validation\Rule;

/**
 * Валидация на стороне контроллера (Validation Rules).
 * Один класс используется и для store, и для update.
 */
class MessierObjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Приводим обозначение к виду, в котором оно хранится в БД («m 10» → «M10»). */
    protected function prepareForValidation(): void
    {
        if (is_string($this->name)) {
            $this->merge([
                'name' => mb_strtoupper(preg_replace('/\s+/u', '', $this->name)),
            ]);
        }
    }

    public function rules(): array
    {
        $object = $this->route('object'); // модель при update, null при store

        return [
            'constellation' => ['required', 'string', 'max:100', 'regex:/^[А-Яа-яЁё0-9\s\-]+$/u'],
            'name' => [
                'required', 'string', 'max:20',
                // обозначение уникально среди не удалённых объектов
                Rule::unique('messier_objects', 'name')
                    ->ignore($object?->id)
                    ->whereNull('deleted_at'),
            ],
            'type' => ['required', Rule::in(MessierObject::TYPES)],
            'short_description' => [
                'required', 'string', 'max:500',
                $this->wordCount(MessierObject::DESCRIPTION_MIN_WORDS, MessierObject::DESCRIPTION_MAX_WORDS, 'Краткое описание'),
            ],
            'details' => [
                'required', 'string', 'max:5000',
                $this->wordCount(MessierObject::DETAILS_MIN_WORDS, MessierObject::DETAILS_MAX_WORDS, 'Подробное описание'),
            ],
            'catalogued_at' => ['nullable', 'date', 'before_or_equal:today'],
            // при создании картинка обязательна, при редактировании — по желанию
            'image' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'image', 'mimes:jpeg,jpg,png,webp', 'max:5120',
            ],
        ];
    }

    /** Правило: количество слов в тексте от $min до $max. */
    private function wordCount(int $min, int $max, string $label): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($min, $max, $label) {
            $words = preg_match_all('/[\p{L}\p{N}]+/u', (string) $value);

            if ($words < $min || $words > $max) {
                $fail("{$label} должно содержать от {$min} до {$max} слов (сейчас: {$words}).");
            }
        };
    }

    public function messages(): array
    {
        return [
            'required' => 'Поле «:attribute» обязательно для заполнения.',
            'max.string' => 'Поле «:attribute» не должно быть длиннее :max символов.',
            'name.unique' => 'Объект с таким обозначением уже есть в каталоге.',
            'constellation.regex' => 'Созвездие: допустимы только русские буквы, цифры, пробел и дефис.',
            'type.in' => 'Выберите тип объекта из списка.',
            'catalogued_at.date' => 'Укажите корректную дату.',
            'catalogued_at.before_or_equal' => 'Дата не может быть в будущем.',
            'image.image' => 'Файл должен быть изображением.',
            'image.mimes' => 'Допустимые форматы: JPG, PNG, WEBP.',
            'image.max' => 'Размер картинки не должен превышать 5 МБ.',
            'image.uploaded' => 'Не удалось загрузить файл (возможно, он слишком большой).',
        ];
    }

    public function attributes(): array
    {
        return [
            'constellation' => 'Созвездие',
            'name' => 'Обозначение',
            'type' => 'Тип объекта',
            'short_description' => 'Краткое описание',
            'details' => 'Подробное описание',
            'catalogued_at' => 'Дата внесения в каталог',
            'image' => 'Изображение',
        ];
    }
}
