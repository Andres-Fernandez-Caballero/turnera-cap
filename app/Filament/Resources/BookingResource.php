<?php

namespace App\Filament\Resources;

use App\Core\UseCases\Locations\GetFormatedTimeSlotByLocationId;
use App\Core\UseCases\Locations\GetTimeSlotsByLocationId;
use App\Filament\Resources\BookingResource\Pages;;

use App\Models\Booking;
use App\Models\Location;
use App\Models\Payments\Enums\PaymentStatus;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->label('Fecha')
                    ->default(fn() => now()->format('Y-m-d'))
                    ->weekStartsOnMonday()
                    ->minDate(fn() => now())
                    ->live()
                    ->required(),
                Forms\Components\Select::make('location_id')
                    ->label('Locación')
                    ->options(fn() => Location::all()->pluck('name', 'id'))
                    ->live()
                    ->required(),
                Forms\Components\Select::make('timeSlots')
                    ->multiple()
                    ->reactive()
                    ->live()
                    ->label('Horario')
                    ->hidden(fn(Forms\Get $get) => $get('location_id') == null || $get('date') == null)
                    ->options(function (Forms\Get $get) {
                        if ($get('location_id') && $get('date')) {
                            return app(GetFormatedTimeSlotByLocationId::class)
                                ->execute((int)$get('location_id'), $get('date'));
                        }
                        return [];
                    })
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user')
                    ->label('Usuario')
                    ->options(fn() => User::all()->pluck('name', 'id'))
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email'),
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirmar contraseña'),
                        Forms\Components\TextInput::make('dni')
                            ->label('DNI'),
                    ])
                    ->required(),

                Forms\Components\Repeater::make('invites')
                    ->label('Cantidad de personas')
                    ->schema([
                        Forms\Components\TextInput::make('name'),
                        Forms\Components\TextInput::make('last_name'),
                        Forms\Components\TextInput::make('dni'),
                    ]),

                Forms\Components\DateTimePicker::make('check_in_at')
                    ->label('Check-in')
                    ->placeholder('Sin check-in')
                    ->readonly(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nombre'),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),

                Tables\Columns\TextColumn::make('people_count')
                    ->label('Cantidad de personas'),

                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha'),

                Tables\Columns\TextColumn::make('check_in_at')
                    ->label('Check-in')
                    ->dateTime()
                    ->placeholder('Sin check-in'),

                TextColumn::make('payment.payment_method')
                    ->label('Medio de pago'),

                Tables\Columns\TextColumn::make('timeSlots.start_time')
                    ->label('Hora de inicio')
                    ->badge(),

                Tables\Columns\TextColumn::make('timeSlots.end_time')
                    ->label('Hora de fin')
                    ->badge(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Estado de pago')
                    ->badge()
                    ->color(fn(PaymentStatus $state) => match ($state) {
                        PaymentStatus::PENDING => 'warning',
                        PaymentStatus::PENDING_APPROVAL => 'warning',
                        PaymentStatus::APPROVED => 'success',
                        PaymentStatus::REJECTED => 'danger'
                    }),

            ])
            ->filters([
                //filter by start_time where is now date

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('invites_data')
                    ->infolist([
                        RepeatableEntry::make('invites')
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('last_name'),
                                TextEntry::make('dni')
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                    ]),

                Tables\Actions\Action::make('payment_data')
                    ->label('Factura')
                    ->icon('heroicon-o-document-text')
                    ->modalHeading('Detalle de Factura')
                    ->fillForm(fn (Booking $record): array => [
                        'payment_status' => $record->payment?->status->value ?? PaymentStatus::PENDING->value,
                    ])
                    ->form([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Placeholder::make('payment_method')
                                    ->label('Medio de pago')
                                    ->content(fn (Booking $record) => $record->payment?->payment_method ?? 'N/A'),

                                Forms\Components\Select::make('payment_status')
                                    ->label('Estado de Pago')
                                    ->options(PaymentStatus::class)
                                    ->required(),

                                Forms\Components\Placeholder::make('reference')
                                    ->label('Referencia')
                                    ->content(fn (Booking $record) => $record->payment?->reference ?? 'N/A'),

                                Forms\Components\Placeholder::make('payment_code')
                                    ->label('Código de pago')
                                    ->content(fn (Booking $record) => $record->payment?->payment_code ?? 'N/A'),

                                Forms\Components\Placeholder::make('amount')
                                    ->label('Total')
                                    ->content(fn (Booking $record) => $record->payment ? 'ARS $ ' . number_format($record->payment->amount, 2) : 'N/A'),
                            ])
                            ->columns(2),
                    ])
                    ->action(function (Booking $record, array $data): void {
                        if ($record->payment) {
                            $record->payment->update([
                                'status' => $data['payment_status'],
                            ]);
                        } else {
                            // Si no hay pago, podríamos crearlo o avisar. 
                            // Por ahora solo actualizamos si existe.
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
