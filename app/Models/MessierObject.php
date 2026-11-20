<?php

namespace App\Models;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/**
 * Объект каталога Мессье. Таблица: messier_objects (по соглашению Laravel).
 */
class MessierObject extends Model
{
    use SoftDeletes; // «удаление» только проставляет deleted_at

    /** Допустимые типы объектов (выпадающий список в форме). */
    public const TYPES = [
        'Шаровое скопление',
        'Рассеянное скопление',
        'Эмиссионная туманность',
        'Отражательная туманность',
        'Тёмная туманность',
        'Планетарная туманность',
        'Остаток сверхновой',
        'Спиральная галактика',
        'Эллиптическая галактика',
        'Линзовидная галактика',
        'Галактика',
        'Звёздное облако',
    ];

    /** Сколько слов должно быть в кратком описании. */
    public const DESCRIPTION_MIN_WORDS = 5;
    public const DESCRIPTION_MAX_WORDS = 40;

    /** Сколько слов должно быть в подробном описании. */
    public const DETAILS_MIN_WORDS = 15;
    public const DETAILS_MAX_WORDS = 200;

    protected $fillable = [
        'user_id',
        'constellation',
        'name',
        'type',
        'short_description',
        'details',
        'catalogued_at',
        'image_path',
    ];

    // ---------- События модели (Closures): права проверяются на уровне модели ----------

    protected static function booted(): void
    {
        // Создание: автором становится текущий пользователь
        static::creating(function (MessierObject $object) {
            if (! $object->user_id && Auth::check()) {
                $object->user_id = Auth::id();
            }

            if (! app()->runningInConsole() && ! Auth::check()) {
                throw new AuthorizationException('Создавать объекты могут только авторизованные пользователи.');
            }
        });

        // Изменение: только автор или администратор
        static::updating(function (MessierObject $object) {
            static::ensureAllowed('update-object', $object);
        });

        // Удаление: мягкое — автор или админ, полное (forceDelete) — только админ
        static::deleting(function (MessierObject $object) {
            static::ensureAllowed(
                $object->isForceDeleting() ? 'force-delete-object' : 'delete-object',
                $object
            );
        });

        // Восстановление мягко удалённого объекта — только админ
        static::restoring(function (MessierObject $object) {
            static::ensureAllowed('restore-object', $object);
        });
    }

    /**
     * Проверка права через Gate. Из консоли (seeder, tinker, artisan) проверка
     * не выполняется — там нет авторизованного пользователя.
     */
    protected static function ensureAllowed(string $ability, MessierObject $object): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (Gate::denies($ability, $object)) {
            throw new AuthorizationException('Недостаточно прав для этого действия.');
        }
    }

    // ---------- Связи ----------

    /** Автор объекта. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Комментарии к объекту. */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // ---------- Мутаторы / аксессоры (Attribute) ----------

    /** Обозначение всегда хранится заглавными без пробелов: " m 10 " -> "M10". */
    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => mb_strtoupper(preg_replace('/\s+/u', '', $value)),
        );
    }

    /** Созвездие — с заглавной буквы: "змееносец" -> "Змееносец". */
    protected function constellation(): Attribute
    {
        return Attribute::make(
            set: function (string $value) {
                $value = trim($value);

                return mb_strtoupper(mb_substr($value, 0, 1)).mb_substr($value, 1);
            },
        );
    }

    /**
     * Поле даты: при записи приводим к формату БД (Y-m-d),
     * при чтении отдаём объект Carbon.
     */
    protected function cataloguedAt(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Carbon::parse($value) : null,
            set: fn ($value) => $value ? Carbon::parse($value)->toDateString() : null,
        );
    }

    // ---------- Вспомогательные свойства ----------

    /** Дата словами: «29 мая 1764» (или «—», если не указана). */
    public function getCataloguedAtTextAttribute(): string
    {
        return $this->catalogued_at
            ? $this->catalogued_at->locale('ru')->translatedFormat('j F Y')
            : '—';
    }

    /** Публичный URL картинки из storage (или заглушка). */
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            // asset() берёт адрес из текущего запроса, поэтому работает на любом хосте/порту
            // (ссылка public/storage создаётся командой php artisan storage:link)
            return asset('storage/'.$this->image_path);
        }

        return asset('img/placeholder.svg');
    }

    /** Заголовок для модального окна и страницы: «Змееносец — M10». */
    public function getFullTitleAttribute(): string
    {
        return $this->constellation.' — '.$this->name;
    }

    /**
     * Подробный текст в HTML. Текст экранируется (защита от XSS), затем
     * разметка подсказок [слово|пояснение] превращается в <span class="hint">
     * для Bootstrap Popover, а пустые строки разделяют абзацы.
     */
    public function getDetailsHtmlAttribute(): string
    {
        $paragraphs = preg_split('/\R{2,}/u', trim($this->details));

        return collect($paragraphs)
            ->map(function (string $paragraph) {
                $paragraph = nl2br(e($paragraph));

                $paragraph = preg_replace_callback(
                    '/\[([^\[\]|]+)\|([^\[\]]+)\]/u',
                    fn ($m) => '<span class="hint" data-bs-title="'.$m[1].'" data-bs-content="'.$m[2].'">'.$m[1].'</span>',
                    $paragraph
                );

                return '<p>'.$paragraph.'</p>';
            })
            ->implode("\n");
    }
}
