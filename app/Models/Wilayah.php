<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table      = 'wilayah';
    protected $primaryKey = 'kode';
    public    $incrementing = false;
    protected $keyType    = 'string';
    public    $timestamps = false;

    protected $fillable = ['kode', 'nama'];

    // ── Level constants ───────────────────────────────────────────────────────

    public const LEVEL_PROVINSI   = 1; // kode length: 2
    public const LEVEL_KABUPATEN  = 2; // kode length: 5  (XX.XX)
    public const LEVEL_KECAMATAN  = 3; // kode length: 8  (XX.XX.XX)
    public const LEVEL_KELURAHAN  = 4; // kode length: 13 (XX.XX.XX.XXXX)

    private const LEVEL_MAP = [
        2  => self::LEVEL_PROVINSI,
        5  => self::LEVEL_KABUPATEN,
        8  => self::LEVEL_KECAMATAN,
        13 => self::LEVEL_KELURAHAN,
    ];

    private const LEVEL_LABEL = [
        self::LEVEL_PROVINSI  => 'Provinsi',
        self::LEVEL_KABUPATEN => 'Kabupaten/Kota',
        self::LEVEL_KECAMATAN => 'Kecamatan',
        self::LEVEL_KELURAHAN => 'Kelurahan/Desa',
    ];

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getLevelAttribute(): int
    {
        return self::LEVEL_MAP[strlen($this->kode)] ?? 0;
    }

    public function getLevelNameAttribute(): string
    {
        return self::LEVEL_LABEL[$this->level] ?? 'Tidak dikenal';
    }

    public function getParentKodeAttribute(): ?string
    {
        return match (strlen($this->kode)) {
            5  => substr($this->kode, 0, 2),  // Kabupaten → Provinsi
            8  => substr($this->kode, 0, 5),  // Kecamatan → Kabupaten
            13 => substr($this->kode, 0, 8),  // Kelurahan → Kecamatan
            default => null,
        };
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    /** Parent satu level di atas */
    public function parent(): ?self
    {
        $parentKode = $this->parent_kode;

        return $parentKode ? static::find($parentKode) : null;
    }

    /** Semua anak langsung satu level di bawah */
    public function children(): Builder
    {
        $childLen = strlen($this->kode) + 3;

        return static::query()
            ->whereRaw('LENGTH(kode) = ?', [$childLen])
            ->where('kode', 'LIKE', $this->kode . '.%')
            ->orderBy('kode');
    }

    /** Rantai parent dari root sampai node ini (inklusif) */
    public function parentChain(): Collection
    {
        $chain  = new Collection();
        $current = $this;

        while ($parent = $current->parent()) {
            $chain->prepend($parent);
            $current = $parent;
        }

        return $chain->push($this);
    }

    // ── Query Scopes ──────────────────────────────────────────────────────────

    public function scopeProvinsi(Builder $query): Builder
    {
        return $query->whereRaw('LENGTH(kode) = 2')->orderBy('nama');
    }

    public function scopeKabupaten(Builder $query, ?string $provinsiKode = null): Builder
    {
        $query->whereRaw('LENGTH(kode) = 5')->orderBy('nama');

        if ($provinsiKode) {
            $query->where('kode', 'LIKE', $provinsiKode . '.%');
        }

        return $query;
    }

    public function scopeKecamatan(Builder $query, ?string $kabupatenKode = null): Builder
    {
        $query->whereRaw('LENGTH(kode) = 8')->orderBy('nama');

        if ($kabupatenKode) {
            $query->where('kode', 'LIKE', $kabupatenKode . '.%');
        }

        return $query;
    }

    public function scopeKelurahan(Builder $query, ?string $kecamatanKode = null): Builder
    {
        $query->whereRaw('LENGTH(kode) = 13')->orderBy('nama');

        if ($kecamatanKode) {
            $query->where('kode', 'LIKE', $kecamatanKode . '.%');
        }

        return $query;
    }

    // ── Static helpers (untuk controller / select options) ────────────────────

    /** Semua provinsi sebagai [kode => nama] */
    public static function optionProvinsi(): array
    {
        return static::provinsi()->pluck('nama', 'kode')->toArray();
    }

    /** Kabupaten/kota dalam provinsi sebagai [kode => nama] */
    public static function optionKabupaten(string $provinsiKode): array
    {
        return static::kabupaten($provinsiKode)->pluck('nama', 'kode')->toArray();
    }

    /** Kecamatan dalam kabupaten sebagai [kode => nama] */
    public static function optionKecamatan(string $kabupatenKode): array
    {
        return static::kecamatan($kabupatenKode)->pluck('nama', 'kode')->toArray();
    }

    /** Kelurahan/desa dalam kecamatan sebagai [kode => nama] */
    public static function optionKelurahan(string $kecamatanKode): array
    {
        return static::kelurahan($kecamatanKode)->pluck('nama', 'kode')->toArray();
    }

    // ── Format helper ─────────────────────────────────────────────────────────

    /**
     * Konversi kode BPS tanpa titik ke format wilayah (misal: '1101' → '11.01').
     * Berguna saat menghubungkan Kafilah.kode_wilayah ke tabel wilayah.
     */
    public static function fromBpsKode(string $bpsKode): ?self
    {
        $len = strlen($bpsKode);

        $formatted = match ($len) {
            2  => $bpsKode,
            4  => substr($bpsKode, 0, 2) . '.' . substr($bpsKode, 2, 2),
            6  => substr($bpsKode, 0, 2) . '.' . substr($bpsKode, 2, 2) . '.' . substr($bpsKode, 4, 2),
            10 => substr($bpsKode, 0, 2) . '.' . substr($bpsKode, 2, 2) . '.' . substr($bpsKode, 4, 2) . '.' . substr($bpsKode, 6, 4),
            default => null,
        };

        return $formatted ? static::where('kode', $formatted)->first() : null;
    }
}
