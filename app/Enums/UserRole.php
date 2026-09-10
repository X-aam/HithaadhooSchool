<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Author = 'author';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Editor => 'Editor',
            self::Author => 'Author',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Full access, including managing users.',
            self::Editor => 'Manage news, announcements and all site pages/content.',
            self::Author => 'Write and manage news & blog articles only.',
        };
    }

    /** Can manage other users (the user manager). */
    public function canManageUsers(): bool
    {
        return $this === self::Admin;
    }

    /** Can manage announcements and site pages/content (incl. navigation). */
    public function canManageContent(): bool
    {
        return in_array($this, [self::Admin, self::Editor], true);
    }

    /** Can manage news & blog articles. */
    public function canManageNews(): bool
    {
        return in_array($this, [self::Admin, self::Editor, self::Author], true);
    }
}
