<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Búsqueda de texto sin distinguir mayúsculas e igual en ambos motores:
 * LIKE distingue mayúsculas en PostgreSQL y no en MySQL, por eso se compara
 * LOWER(columna) con el término en minúsculas (CLAUDE.md §10.1.1).
 */
trait SearchesText
{
    /** @param  list<string>  $columns  Nombres de columna fijos del código (nunca del usuario). */
    public function scopeSearch(Builder $query, ?string $term, array $columns): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $like = '%'.addcslashes(mb_strtolower($term), '%_\\').'%';

        $query->where(function (Builder $q) use ($columns, $like) {
            foreach ($columns as $column) {
                $q->orWhereRaw('LOWER('.$q->getGrammar()->wrap($column).') LIKE ?', [$like]);
            }
        });
    }
}
