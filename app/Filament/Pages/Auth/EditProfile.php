<?php

namespace App\Filament\Pages\Auth;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Get;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Hash;

class EditProfile extends BaseEditProfile
{
    protected function getCurrentPasswordFormComponent(): Component
    {
        return \Filament\Forms\Components\TextInput::make('current_password')
            ->label('Current password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required(fn (Get $get): bool => filled($get('password')))
            ->visible(fn (Get $get): bool => filled($get('password')))
            ->rule(fn (): Closure => function (string $attribute, $value, Closure $fail) {
                if (! Hash::check($value, $this->getUser()->password)) {
                    $fail('The current password is incorrect.');
                }
            })
            ->dehydrated(false);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data')
                    ->inlineLabel(! static::isSimple()),
            ),
        ];
    }
}
