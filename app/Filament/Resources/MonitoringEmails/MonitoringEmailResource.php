<?php

namespace App\Filament\Resources\MonitoringEmails;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\MonitoringEmail;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\MonitoringEmails\Pages\ManageMonitoringEmails;

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
                    ->columnSpanFull()
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
                    ->columnSpanFull()
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
                        TextEntry::make('status_code')
                            ->label('Status Code')
                            ->badge()
                            ->color(fn (?int $state): string => $state === 250 ? 'success' : ($state ? 'danger' : 'gray')),
                    ])->columns(4),
                Section::make('Content')
                    ->columnSpanFull()
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
                TextColumn::make('status_code')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?int $state): string => $state === 250 ? 'success' : ($state ? 'danger' : 'gray')),
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
