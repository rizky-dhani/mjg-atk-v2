<?php

namespace App\Filament\Resources\MonitoringEmails;

use App\Filament\Resources\MonitoringEmails\Pages\ManageMonitoringEmails;
use App\Models\MonitoringEmail;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MonitoringEmailResource extends Resource
{
    protected static ?string $model = MonitoringEmail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Envelope;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Read-only resource
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Email Details')
                    ->components([
                        TextEntry::make('created_at')
                            ->label('Sent At')
                            ->dateTime(),
                        TextEntry::make('from'),
                        TextEntry::make('to'),
                        TextEntry::make('cc')
                            ->placeholder('No CC'),
                        TextEntry::make('bcc')
                            ->placeholder('No BCC'),
                        TextEntry::make('subject'),
                    ])->columns(2),
                Section::make('Action Details')
                    ->components([
                        TextEntry::make('action_type')
                            ->label('Action')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Approve' => 'success',
                                'Reject' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('actionBy.name')
                            ->label('Action By'),
                        TextEntry::make('action_at')
                            ->label('Action At')
                            ->dateTime(),
                    ])->columns(3),
                Section::make('Content')
                    ->components([
                        TextEntry::make('content_html')
                            ->label('HTML Content')
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('content_text')
                            ->label('Text Content')
                            ->prose()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('to')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('action_type')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approve' => 'success',
                        'Reject' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('actionBy.name')
                    ->label('By'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMonitoringEmails::route('/'),
        ];
    }
}
