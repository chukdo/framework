<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;

class ValidatorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $validator = $this->app->make('\Chukdo\Validation\Validator', true);

        $validator->registerFilter(new \Chukdo\Validation\Filter\BoolFilter());
        $validator->registerFilter(new \Chukdo\Validation\Filter\FloatFilter());
        $validator->registerFilter(new \Chukdo\Validation\Filter\IntFilter());
        $validator->registerFilter(new \Chukdo\Validation\Filter\PhoneFilter());
        $validator->registerFilter(new \Chukdo\Validation\Filter\StriptagsFilter());

        $validator->registerValidator(new \Chukdo\Validation\Validate\BoolValidate());
        $validator->registerValidator(new \Chukdo\Validation\Validate\CsrfValidate());
        $validator->registerValidator(new \Chukdo\Validation\Validate\EmailValidate());
        $validator->registerValidator(new \Chukdo\Validation\Validate\FileValidate());
        $validator->registerValidator(new \Chukdo\Validation\Validate\FloatValidate());
        $validator->registerValidator(new \Chukdo\Validation\Validate\IntValidate());
        $validator->registerValidator(new \Chukdo\Validation\Validate\PhoneValidate());
        $validator->registerValidator(new \Chukdo\Validation\Validate\StringValidate());
        $validator->registerValidator(new \Chukdo\Validation\Validate\UrlValidate());
        $validator->registerValidator(new \Chukdo\Validation\Validate\ZipcodeValidate());
    }
}
