<?php

namespace App\Policies;

use App\Models\Mustahik;
use App\Models\User;

class MustahikPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('mustahik.view');
    }

    public function view(User $user, Mustahik $mustahik): bool
    {
        if (!$user->can('mustahik.view')) return false;

        // Admin gampong hanya bisa lihat mustahik gampongnya
        if ($user->hasRole('admin_gampong')) {
            return $mustahik->kode_desa === $user->kode_wilayah;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->can('mustahik.create');
    }

    public function update(User $user, Mustahik $mustahik): bool
    {
        if (!$user->can('mustahik.edit')) return false;

        if ($user->hasRole('admin_gampong')) {
            return $mustahik->kode_desa === $user->kode_wilayah;
        }

        return true;
    }

    public function delete(User $user, Mustahik $mustahik): bool
    {
        if (!$user->can('mustahik.delete')) return false;

        if ($user->hasRole('admin_gampong')) {
            return $mustahik->kode_desa === $user->kode_wilayah;
        }

        return true;
    }

    public function nonaktifkan(User $user, Mustahik $mustahik): bool
    {
        return $user->can('mustahik.nonaktifkan');
    }
}
