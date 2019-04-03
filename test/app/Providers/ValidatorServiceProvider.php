<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;

class ValidatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->make('\Chukdo\Validation\Validator', true)
            ->registerFilters([
                new \Chukdo\Validation\Filter\BoolFilter(),
                new \Chukdo\Validation\Filter\FloatFilter(),
                new \Chukdo\Validation\Filter\IntFilter(),
                new \Chukdo\Validation\Filter\PhoneFilter(),
                new \Chukdo\Validation\Filter\StriptagsFilter(),
            ])
            ->registerValidators([
                new \Chukdo\Validation\Validate\BoolValidate(),
                new \Chukdo\Validation\Validate\CsrfValidate(),
                new \Chukdo\Validation\Validate\EmailValidate(),
                new \Chukdo\Validation\Validate\FileValidate(),
                new \Chukdo\Validation\Validate\FloatValidate(),
                new \Chukdo\Validation\Validate\IntValidate(),
                new \Chukdo\Validation\Validate\PhoneValidate(),
                new \Chukdo\Validation\Validate\StringValidate(),
                new \Chukdo\Validation\Validate\UrlValidate(),
                new \Chukdo\Validation\Validate\ZipcodeValidate(),
            ]);
    }
}
