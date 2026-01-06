<?php

namespace App\Filament\Resources\Counterparties\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Http;

class CounterpartyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()

                    ->schema([

                        TextInput::make('name')
                            ->label('Nome')
                            ->columnSpanFull()
                            ->required(),
        
                        // Row: Document, Email, Company
                        Grid::make(3)
                            ->schema([
                                TextInput::make('document')
                                    ->label('CPF ou CNPJ (Opcional)')
                                    ->mask(RawJs::make(<<<'JS'
                                        $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                                    JS)),
                                TextInput::make('email')
                                    ->label('Email (Opcional)')
                                    ->email(),
                                 TextInput::make('company_name')
                                    ->label('Empresa (Opcional)'),
                            ]),
        
                        // Row: Phone Contact
                        \Filament\Schemas\Components\Grid::make(2) // Reduced grid, but let's stick to simple layout or columns
                            ->columns(2)
                            ->schema([
                                TextInput::make('mobile_phone')
                                    ->label('Celular (Opcional)')
                                    ->mask('(99) 99999-9999'),
                                TextInput::make('phone')
                                    ->label('Fone (Opcional)')
                                    ->mask('(99) 9999-9999'),
                            ]),
        
                        // Address
                        Grid::make(4)
                            ->schema([
                                 TextInput::make('zip_code')
                                    ->label('CEP (Opcional)')
                                    ->mask('99999-999')
                                    ->live(true)
                                    ->afterStateUpdated(function (Set $set, string $state) {
                                        $response = Http::get('https://viacep.com.br/ws/'. $state .'/json/');
                                        $set('street', $response->json('logradouro'));
                                        $set('district', $response->json('bairro'));
                                        $set('city', $response->json('localidade'));
                                        $set('state', $response->json('uf'));
                                    }),
                                 TextInput::make('street')
                                    ->label('Rua (Opcional)')
                                    ->columnSpan(3),
                            ]),
        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('number')
                                    ->label('Número (Opcional)')
                                    ->columnSpan(1),
                                TextInput::make('complement')
                                    ->columnSpan(2)
                                    ->label('Complemento ou Referência(Opcional)'),
                            ]),
                        
                        Grid::make(4)
                            ->schema([
                                TextInput::make('district')
                                    ->label('Bairro (Opcional)')
                                    ->columnSpan(1),
                                TextInput::make('city')
                                    ->label('Cidade (Opcional)')
                                    ->columnSpan(2),
                                Select::make('state')
                                    ->options([
                                        'AC' => 'AC',
                                        'AL' => 'AL',
                                        'AP' => 'AP',
                                        'AM' => 'AM',
                                        'BA' => 'BA',
                                        'CE' => 'CE',
                                        'DF' => 'DF',
                                        'ES' => 'ES',
                                        'GO' => 'GO',
                                        'MA' => 'MA',
                                        'MT' => 'MT',
                                        'MS' => 'MS',
                                        'MG' => 'MG',
                                        'PA' => 'PA',
                                        'PB' => 'PB',
                                        'PR' => 'PR',
                                        'PE' => 'PE',
                                        'PI' => 'PI',
                                        'RJ' => 'RJ',
                                        'RN' => 'RN',
                                        'RS' => 'RS',
                                        'RO' => 'RO',
                                        'RR' => 'RR',
                                        'SC' => 'SC',
                                        'SP' => 'SP',
                                        'SE' => 'SE',
                                        'TO' => 'TO',
                                    ])
                                    ->placeholder('Selecione')
                                    ->label('Estado (Opcional)')
                                    ->columnSpan(1), 
                            ]),

                        // Legal
                        Grid::make(2)
                            ->schema([
                                TextInput::make('municipal_registration')
                                    ->label('Inscrição Municipal (Opcional)'),
                                TextInput::make('state_registration')
                                    ->label('Inscrição Estadual (Opcional)'),
                            ]),
        
                        \Filament\Forms\Components\TagsInput::make('additional_emails')
                            ->label('Emails adicionais (Opcional)')
                            ->placeholder('Digite o email e pressione "Enter" para adicioná-lo.'),
        
                        \Filament\Forms\Components\Textarea::make('observations')
                            ->label('Observações (Opcional)')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                // Section 1: Main Info
            ]);
    }
}
