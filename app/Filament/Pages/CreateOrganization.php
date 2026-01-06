<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateOrganization extends RegisterTenant
{
    public ?array $data = [];

    public static function getLabel(): string
    {
        return "Cadastrar Financeiro";
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', str($state)->slug()))
                    ->label('Nome da Organização')
                    ->required()
                    ->maxLength(255),
                Hidden::make('slug')
                    ->required()
            ])->statePath('data');
    }

    protected function handleRegistration(array $data): Model
    {
        return Auth::user()->organizations()->create($data);
    }
}
