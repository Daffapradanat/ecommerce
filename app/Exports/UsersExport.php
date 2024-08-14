<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $isTemplate;

    public function __construct($isTemplate = false)
    {
        $this->isTemplate = $isTemplate;
    }

    public function collection()
    {
        if ($this->isTemplate) {
            return collect([]);
        }

        return User::select('id','name', 'email', 'image')
            ->get()
            ->map(function ($user) {
                $user->image = url('storage/users/' . basename($user->image));
                return $user;
            });
    }

    public function headings(): array
    {
        return [
            'Id',
            'Name',
            'Email',
            'Image URL',
        ];
    }
}
