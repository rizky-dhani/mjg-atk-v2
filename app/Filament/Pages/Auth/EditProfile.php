<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Keamanan Akun')
                    ->description('Silakan ganti kata sandi default Anda untuk mengamankan akun sebelum menggunakan website.')
                    ->hidden(fn () => auth()->user()->has_changed_password)
                    ->icon('heroicon-m-shield-exclamation')
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->columnSpanFull(),
                Section::make('Informasi Profil')
                    ->aside()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->columnSpanFull()
                    ->hidden(fn () => ! auth()->user()->has_changed_password)
                    ->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        parent::save();

        $user = auth()->user();

        if (! $user->has_changed_password) {
            $user->update([
                'has_changed_password' => true,
            ]);

            Notification::make()
                ->title('Akun Teramankan')
                ->body('Terima kasih telah memperbarui kata sandi Anda. Sekarang Anda dapat menggunakan seluruh fitur website.')
                ->success()
                ->send();

            $this->redirect(route('filament.dashboard.pages.dashboard'));
        }
    }
}
