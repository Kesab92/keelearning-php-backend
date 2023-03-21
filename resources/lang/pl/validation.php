<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted'             => 'Należy zaakceptować :attribute.',
    'active_url'           => ':attribute nie jest poprawnym adresem internetowym.',
    'after'                => ':attribute musi być datą po :date.',
    'after_or_equal'       => ':attribute musi być datą po :datelub równą :date.',
    'alpha'                => ':attribute może składać się tylko z liter.',
    'alpha_dash'           => ':attribute może składać się wyłącznie z liter, cyfr, myślników i podkreślników.',
    'alpha_num'            => ':attribute może składać się wyłącznie z liter i cyfr.',
    'array'                => ':attribute musi być tablicą.',
    'before'               => ':attribute musi być datą przed:date.',
    'before_or_equal'      => ':attribute musi być datą przed :date lub równą :date.',
    'between'              => [
        'numeric' => ':attribute musi leżeć pomiędzy :min a :max.',
        'file'    => ':attribute musi mieć rozmiar pomiędzy :min a :max kilobajtów.',
        'string'  => ':attribute musi mieć długość pomiędzy :min a :max znaków.',
        'array'   => ':attribute musi posiadać pomiędzy :min a :max elementów.',
    ],
    'boolean'              => ":attribute musi przyjmować wartość 'true' lub 'false'.",
    'confirmed'            => ':attribute nie zgadza się z potwierdzeniem.',
    'date'                 => ':attribute musi być poprawną datą.',
    'date_format'          => ':attribute nie odpowiada poprawnemu formatowi :format.',
    'different'            => ':attribute i :other muszą się różnić.',
    'digits'               => ':attribute musi posiadać :digits pozycji.',
    'digits_between'       => ':attribute musi posiadać pomiędzy :min a :max pozycji.',
    'dimensions'           => ':attribute posiada niepoprawne wymiary obrazu.',
    'distinct'             => ':attribute zawiera już istniejącą wartość.',
    'email'                => ':attribute musi być poprawnym adresem e-mail.',
    'exists'               => 'Wybrana dla :attribute wartość jest niepoprawna.',
    'file'                 => ':attribute musi być plikiem.',
    'filled'               => ':attribute musi być wypełniony.',
    'gt'                   => [
        'numeric' => ':attribute musi być przynajmniej :value.',
        'file'    => ':attribute musi mieć rozmiar przynajmniej :value kilobajtów.',
        'string'  => ':attribute musi mieć długość przynajmniej :value znaków.',
        'array'   => ':attribute musi posiadać przynajmniej :value elementów.',
    ],
    'gte'                  => [
        'numeric' => ':attribute musi być większy lub równy :value.',
        'file'    => ':attribute musi być większy lub równy :value kilobajtów.',
        'string'  => ':attribute musi być większy lub równy :value znaków.',
        'array'   => ':attribute musi być większy lub równy :value elementów.',
    ],
    'image'                => ':attribute musi być obrazem.',
    'in'                   => 'Wybrana dla :attribute wartość jest niepoprawna.',
    'in_array'             => 'Wybrana dla :attribute wartość nie występuje w :other.',
    'integer'              => ':attribute musi być liczbą całkowita.',
    'ip'                   => ':attribute musi być prawidłowym adresem IP.',
    'ipv4'                 => ':attribute musi być prawidłowym adresem IPv4.',
    'ipv6'                 => ':attribute musi być prawidłowym adresem IPv6.',
    'json'                 => ':attribute musi być prawidłowym ciągiem znaków JSON.',
    'lt'                   => [
        'numeric' => ':attribute musi być mniejszy niż :value.',
        'file'    => ':attribute musi być mniejszy niż :value kilobajtów.',
        'string'  => ':attribute musi być mniejszy niż :value znaków.',
        'array'   => ':attribute musi być mniejszy niż :value elementów.',
    ],
    'lte'                  => [
        'numeric' => ':attribute musi być mniejszy lub równy :value.',
        'file'    => ':attribute musi być mniejszy lub równy :value kilobajtów.',
        'string'  => ':attribute musi być mniejszy lub równy :value znaków.',
        'array'   => ':attribute musi być mniejszy lub równy :value elementów.',
    ],
    'max'                  => [
        'numeric' => ':attribute może być maksymalnie :max.',
        'file'    => ':attribute może mieć maksymalny rozmiar :max kilobajtów.',
        'string'  => ':attribute może być maksymalnie :max znaków.',
        'array'   => ':attribute nie może mieć więcej niż :max elementów.',
    ],
    'mimes'                => ':attribute musi posiadać typ pliku :values.',
    'mimetypes'            => ':attribute musi posiadać typ pliku :values.',
    'min'                  => [
        'numeric' => ':attribute musi być przynajmniej :min.',
        'file'    => ':attribute mieć minimalny rozmiar :min kilobajtów.',
        'string'  => ':attribute musi mieć przynajmniej :min znaków.',
        'array'   => ':attribute musi posiadać przynajmniej :min elementów.',
    ],
    'not_in'               => 'Wybrana dla :attribute wartość jest niepoprawna.',
    'not_regex'            => ':attribute ma niewłaściwy format.',
    'numeric'              => ':attribute musi być liczbą.',
    'present'              => ':attribute musi występować.',
    'regex'                => 'Format :attribute jest niepoprawny.',
    'required'             => ':attribute musi być wypełniony.',
    'required_if'          => ':attribute musi być wypełniony, jeśli :other to:value.',
    'required_unless'      => ':attribute musi być wypełniony, jeśli :other nie jest :values.',
    'required_with'        => 'Należy podać:attribute, jeśli wypełniono :values.',
    'required_with_all'    => 'Należy podać:attribute, jeśli wypełniono :values.',
    'required_without'     => 'Należy podać:attribute, jeśli nie wypełniono :values.',
    'required_without_all' => 'Należy podać :attribute, jeśli nie wypełniono żadnego z pól :values.',
    'same'                 => ':attribute i :other muszą się zgadzać.',
    'size'                 => [
        'numeric' => ':attribute musi się równać :size.',
        'file'    => ':attribute musi mieć rozmiar :size kilobajtów.',
        'string'  => ':attribute musi mieć długość :size znaków.',
        'array'   => ':attribute musi mieć dokładnie :size elementów.',
    ],
    'string'               => ':attribute musi być ciągiem znaków.',
    'timezone'             => ':attribute musi poprawną strefą czasową.',
    'unique'               => ':attribute jest już przydzielony.',
    'uploaded'             => 'Nie można było wczytać :attribute.',
    'url'                  => ':attribute musi być adresem URL.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'wiadomość-indywidualna',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name'                  => 'Nazwa',
        'username'              => 'Nazwa użytkownika',
        'email'                 => 'Adres e-mail',
        'first_name'            => 'Imię',
        'last_name'             => 'Nazwisko',
        'password'              => 'Hasło',
        'password_confirmation' => 'Potwierdzenie hasła',
        'city'                  => 'Miasto',
        'country'               => 'Kraj',
        'address'               => 'Adres',
        'phone'                 => 'Numer telefonu',
        'mobile'                => 'Telefon komórkowy',
        'age'                   => 'Wiek',
        'sex'                   => 'Płeć',
        'gender'                => 'Płeć',
        'day'                   => 'dzień',
        'month'                 => 'Miesiąc',
        'year'                  => 'Rok',
        'hour'                  => 'Godzina',
        'minute'                => 'Minuta',
        'second'                => 'Sekunda',
        'title'                 => 'Tytuł',
        'content'               => 'Spis treści',
        'description'           => 'Opis',
        'excerpt'               => 'Wyciąg',
        'date'                  => 'Data',
        'time'                  => 'Godzina',
        'available'             => 'dostępny',
        'size'                  => 'Rozmiar',
    ],
];
