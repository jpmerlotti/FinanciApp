<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class EditOrganization extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return "Atualizar Organização";
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->compact()
                    ->components([
                        TextInput::make('name')
                            ->label('Nome da Organização')
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', str($state)->slug()))
                            ->required()
                            ->maxLength(255),
                        Hidden::make('slug')
                            ->required()
                    ])
            ]);
    }
}
