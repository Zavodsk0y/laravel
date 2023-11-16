<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilePolicy
{
    use HandlesAuthorization;

    public function update(User $user, File $file)
    {
        return $user->id === $file->user_id;
    }

    public function download(User $user, File $file): bool
    {
        return $user->id === $file->user_id || $this->isCoAuthor($user, $file);
    }

    public function fileAccess(User $user, File $file): bool
    {
        return $user->id === $file->user_id;
    }

    private function isCoAuthor(User $user, File $file)
    {
        // Ваша логика проверки, является ли пользователь соавтором файла
        return $file->accesses()->where('user_id', $user->id)->where('type', 'co-author')->exists();
    }
}
