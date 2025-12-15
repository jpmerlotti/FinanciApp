<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->label('Descrição/Título')
                            ->required(),

                        ToggleButtons::make('type')
                            ->label('Tipo de Transação')
                            ->options(TransactionTypes::class)
                            ->inline()
                            ->required(),

                        TextInput::make('amount_cents')
                            ->label('Valor')
                            ->prefix('R$')
                            ->default(0)
                            ->formatStateUsing(fn ($state) => number_format($state / 100, 2, ',', '.'))
                            ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/[^0-9]/', '', $state))
                            ->mask(RawJs::make(<<<'JS'
                                let value = $el.value.replace(/\D/g, '');
                                let formatted = (Number(value) / 100).toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                $el.value = formatted;
                            JS))
                            ->required(),

                        Group::make()
                            ->schema([
                                DatePicker::make('transaction_date')
                                    ->label('Data')
                                    ->required()
                                    ->default(now()),

                                Grid::make()
                                    ->schema([
                                        Toggle::make('repeat_transaction')
                                            ->label('Repetir lançamento?')
                                            ->onColor('success')
                                            ->live()
                                            ->columnSpanFull(),

                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('repeat_count')
                                                    ->hiddenLabel()
                                                    ->placeholder('Qtd.')
                                                    ->prefix('Mais')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->maxValue(360) // Trava de segurança
                                                    ->required(),

                                                Select::make('repeat_interval')
                                                    ->hiddenLabel()
                                                    ->options([
                                                        'weekly'       => 'Semanas',
                                                        'monthly'      => 'Meses',
                                                        'semiannually' => 'Semestres',
                                                        'annually'     => 'Anos',
                                                    ])
                                                    ->default('monthly')
                                                    ->required(),
                                            ])
                                            ->visible(fn (Get $get) => $get('repeat_transaction'))
                                            ->columnSpanFull(),
                                    ])
                                    ->extraAttributes(['class' => 'mt-2 border-t pt-2 border-gray-200 dark:border-gray-700']),
                            ])
                            ->columnSpan(1),
                        Select::make('status')
                            ->options(TransactionStatuses::class)
                            ->default(TransactionStatuses::default())
                            ->prefixIcon(fn (Get $get) => $get('status')?->getIcon())
                            ->required()
                            ->live()
                            ->selectablePlaceholder(false),

                        TextInput::make('recipient')
                            ->label('Beneficiário'),

                        FileUpload::make('payment_proof')
                            ->label('Comprovante')
                            ->disk('s3')
                            ->directory('receipts')
                            ->visibility('private')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->columns([
                        'xs' => 1,
                        'md' => 2,
                        'lg' => 3, // Grid de 3 Colunas
                    ])
            ]);
    }
}
