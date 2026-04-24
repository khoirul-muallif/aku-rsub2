<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi User')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required(),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->required(fn ($context) => $context === 'create')
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->placeholder(fn ($context) => $context === 'edit' ? 'Kosongkan jika tidak diubah' : null),

                    TextInput::make('password_confirmation')
                        ->label('Konfirmasi Password')
                        ->password()
                        ->required(fn ($context) => $context === 'create')
                        ->dehydrated(false),
                ]),

            Section::make('Hak Akses')
                ->schema([
                    Select::make('roles')
                        ->label('Role')
                        ->options(Role::all()->pluck('name', 'name'))
                        ->multiple()
                        ->preload()
                        ->searchable(),
                ]),
        ]);
    }
}