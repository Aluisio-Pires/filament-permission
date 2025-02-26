<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
