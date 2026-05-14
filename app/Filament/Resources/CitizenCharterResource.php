<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CitizenCharterResource\Pages;
use App\Filament\Resources\CitizenCharterResource\RelationManagers;
use App\Models\CitizenCharter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;

class CitizenCharterResource extends Resource
{
    protected static ?string $model = CitizenCharter::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('office_id')
                    ->label('Owner')
                    ->options(function () {
                        $response = Http::get(config('services.api.base_url') . 'public/get-offices');
                        return $response->collect('officeList')->mapWithKeys(function ($data) {
                            return [$data['id'] => $data['officeName']];
                        });
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('required_days')
                    ->label('Prescribed Timeline in Working Days')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('is_external')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Process Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('office_id')
                    ->getStateUsing(function (CitizenCharter $cc) {
                        $response = Http::get(config('services.api.base_url') . 'public/get-offices');
                        $data = $response->collect('officeList')->firstWhere('id', $cc->office_id);
                        return $data['officeName'];
                    })
                    ->label('Owner')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_external')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('required_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                ]),
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
            'index' => Pages\ListCitizenCharters::route('/'),
            'create' => Pages\CreateCitizenCharter::route('/create'),
            'edit' => Pages\EditCitizenCharter::route('/{record}/edit'),
        ];
    }
}
