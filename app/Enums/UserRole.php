<?php

namespace App\Enums;

enum UserRole: string
{
    case StudentStaff = 'student_staff';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::StudentStaff => 'Student / Staff',
            self::Admin => 'Admin',
        };
    }
}
