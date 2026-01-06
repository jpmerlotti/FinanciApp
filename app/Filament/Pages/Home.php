<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Transactions\Widgets\FinancialHealthOverview;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Home extends Dashboard 
{

    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Período')
                    ->compact()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Data Inicial')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('endDate')
                            ->label('Data Final')
                            ->default(now()->endOfMonth()),
                    ])
                    ->columns(2),

                Section::make('Filtros Adicionais')
                    ->compact()
                    ->schema([
                        \Filament\Forms\Components\Select::make('transaction_category_id')
                            ->label('Categoria')
                            ->options(\App\Models\TransactionCategory::pluck('title', 'id'))
                            ->searchable()
                            ->preload(),
                        \Filament\Forms\Components\Select::make('recipient')
                            ->label('Contraparte')
                            ->options(\App\Models\Counterparty::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->columnSpan(2)
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            FinancialHealthOverview::class,
            \App\Filament\Widgets\DueTransactionsWidget::class,
            \App\Filament\Widgets\FinancialStatsOverview::class,
            \App\Filament\Widgets\PayableCounterpartiesWidget::class,
            \App\Filament\Widgets\ReceivableCounterpartiesWidget::class,
        ];
    }
}
