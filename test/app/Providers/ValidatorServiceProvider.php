<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;
use Chukdo\Validation\Validator;
use Chukdo\Validation\Filter;
use Chukdo\Validation\Validate;

class ValidatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->make( Validator::class, true )
                  ->registerFilters( [
                                         new Filter\BoolFilter(),
                                         new Filter\FloatFilter(),
                                         new Filter\IntFilter(),
                                         new Filter\PhoneFilter(),
                                         new Filter\StriptagsFilter(),
                                     ] )
                  ->registerValidators( [
                                            new Validate\BoolValidate(),
                                            new Validate\CsrfValidate(),
                                            new Validate\EmailValidate(),
                                            new Validate\FileValidate(),
                                            new Validate\FloatValidate(),
                                            new Validate\IntValidate(),
                                            new Validate\PhoneValidate(),
                                            new Validate\StringValidate(),
                                            new Validate\UrlValidate(),
                                            new Validate\ZipcodeValidate(),
                                        ] );

        $this->setClassAlias( Validator::class, 'Validator' );
    }
}
