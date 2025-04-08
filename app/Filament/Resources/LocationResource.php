<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers\TimeSlotsRelationManager;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre'),
                Forms\Components\TextInput::make('capacity')
                    ->label('Capacidad')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Forms\Components\TextInput::make('description')
                    ->label('Descripción'),

                Forms\Components\TextInput::make('address')
                    ->label('Dirección'),

                Forms\Components\TextInput::make('pavilion')
                    ->label('Pabellon'),

                    Forms\Components\FileUpload::make('image')
                    ->label('Imagen')
                    ->imageEditor()
                    ->imageResizeMode('cover') // Recorta la imagen para ajustarse al tamaño especificado
                    ->imageCropAspectRatio('1:1') // Mantiene la imagen cuadrada
                    ->imageResizeTargetWidth(1080) // Igualamos el ancho a la altura para que sea 1:1
                    ->imageResizeTargetHeight(1080) // Fijamos la altura en 1080px
                    ->disk('public')
                    ->directory('locations'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\ImageColumn::make('image'),
                Columns\TextColumn::make('name')
                    ->label('Nombre'),
                Columns\TextColumn::make('capacity')
                    ->label('Capacidad')

            ])
            ->filters([
                //
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
            TimeSlotsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
