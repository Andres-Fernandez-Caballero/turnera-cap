<?php

namespace App\Filament\Resources;

use App\Core\UseCases\Locations\GetFormatedTimeSlotByLocationId;
use App\Core\UseCases\Locations\GetTimeSlotsByLocationId;
use App\Filament\Resources\BookingResource\Pages;;
use App\Models\Booking;
use App\Models\Location;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                    ->options(fn () => Location::all()->pluck('name', 'id'))
                    ->live()
                    ->required(),
                Forms\Components\Select::make('timeSlots')
                    ->multiple()
                    ->reactive()
                    ->live()
                    ->label('Horario')
                    ->hidden(fn(Forms\Get $get) => $get('location_id') == null || $get('date') == null)
                    ->options(function(Forms\Get $get) {
                        if ($get('location_id') && $get('date')) {
                            return app(GetFormatedTimeSlotByLocationId::class)
                                ->execute( (int)$get('location_id'), $get('date'));
                        }
                        return [];
                    })
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user')
                    ->label('Usuario')
                    ->options(fn () => User::all()->pluck('name', 'id'))
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

                Tables\Columns\TextColumn::make('timeSlots.start_time')
                ->label('Hora de inicio')
                    ->badge(),

                Tables\Columns\TextColumn::make('timeSlots.end_time')
                ->label('Hora de fin')
                    ->badge(),
                Tables\Columns\TextColumn::make('payment_status ')

            ])
            ->filters([
                //filter by start_time where is now date

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
