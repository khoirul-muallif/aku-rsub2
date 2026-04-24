<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $roles = $this->data['roles'] ?? [];
        $this->record->syncRoles($roles);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->record->roles->pluck('name')->toArray();
        return $data;
    }
}