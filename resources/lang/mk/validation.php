<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Полето :attribute мора да биде прифатено.',
    'accepted_if' => 'Полето :attribute мора да биде прифатено кога :other е :value.',
    'active_url' => 'Полето :attribute не е валиден URL.',
    'after' => 'Полето :attribute мора да биде датум после :date.',
    'after_or_equal' => 'Полето :attribute мора да биде датум после или еднаков на :date.',
    'alpha' => 'Полето :attribute може да содржи само букви.',
    'alpha_dash' => 'Полето :attribute може да содржи само букви, броеви, црти и долни црти.',
    'alpha_num' => 'Полето :attribute може да содржи само букви и броеви.',
    'array' => 'Полето :attribute мора да биде низа.',
    'ascii' => 'Полето :attribute може да содржи само еднобитни алфанумерички знаци и симболи.',
    'before' => 'Полето :attribute мора да биде датум пред :date.',
    'before_or_equal' => 'Полето :attribute мора да биде датум пред или еднаков на :date.',
    'between' => [
        'array' => 'Полето :attribute мора да има помеѓу :min и :max елементи.',
        'file' => 'Полето :attribute мора да биде помеѓу :min и :max килобајти.',
        'numeric' => 'Полето :attribute мора да биде помеѓу :min и :max.',
        'string' => 'Полето :attribute мора да има помеѓу :min и :max карактери.',
    ],
    'boolean' => 'Полето :attribute мора да биде точно или неточно.',
    'can' => 'Полето :attribute содржи неовластена вредност.',
    'confirmed' => 'Потврдата на полето :attribute не се совпаѓа.',
    'contains' => 'Полето :attribute недостасува задолжителна вредност.',
    'current_password' => 'Лозинката е неточна.',
    'date' => 'Полето :attribute не е валиден датум.',
    'date_equals' => 'Полето :attribute мора да биде датум еднаков на :date.',
    'date_format' => 'Полето :attribute не одговара на форматот :format.',
    'decimal' => 'Полето :attribute мора да има :decimal decimal места.',
    'declined' => 'Полето :attribute мора да биде одбиено.',
    'declined_if' => 'Полето :attribute мора да биде одбиено кога :other е :value.',
    'different' => 'Полињата :attribute и :other мора да бидат различни.',
    'digits' => 'Полето :attribute мора да има :digits цифри.',
    'digits_between' => 'Полето :attribute мора да има помеѓу :min и :max цифри.',
    'dimensions' => 'Полето :attribute има невалидни димензии на слика.',
    'distinct' => 'Полето :attribute има дуплирана вредност.',
    'doesnt_end_with' => 'Полето :attribute не смее да завршува со едно од следниве: :values.',
    'doesnt_start_with' => 'Полето :attribute не смее да почнува со едно од следниве: :values.',
    'email' => 'Полето :attribute мора да биде валидна емаил адреса.',
    'ends_with' => 'Полето :attribute мора да завршува со едно од следниве: :values.',
    'enum' => 'Избраната вредност за :attribute е невалидна.',
    'exists' => 'Избраната вредност за :attribute е невалидна.',
    'extensions' => 'Полето :attribute мора да има едно од следниве наставки: :values.',
    'file' => 'Полето :attribute мора да биде датотека.',
    'filled' => 'Полето :attribute мора да има вредност.',
    'gt' => [
        'array' => 'Полето :attribute мора да има повеќе од :value елементи.',
        'file' => 'Полето :attribute мора да биде поголемо од :value килобајти.',
        'numeric' => 'Полето :attribute мора да биде поголемо од :value.',
        'string' => 'Полето :attribute мора да има повеќе од :value карактери.',
    ],
    'gte' => [
        'array' => 'Полето :attribute мора да има :value елементи или повеќе.',
        'file' => 'Полето :attribute мора да биде поголемо или еднакво на :value килобајти.',
        'numeric' => 'Полето :attribute мора да биде поголемо или еднакво на :value.',
        'string' => 'Полето :attribute мора да има :value карактери или повеќе.',
    ],
    'hex_color' => 'Полето :attribute мора да биде валидна хексадецимална боја.',
    'image' => 'Полето :attribute мора да биде слика.',
    'in' => 'Избраната вредност за :attribute е невалидна.',
    'in_array' => 'Полето :attribute мора да постои во :other.',
    'integer' => 'Полето :attribute мора да биде цел број.',
    'ip' => 'Полето :attribute мора да биде валидна IP адреса.',
    'ipv4' => 'Полето :attribute мора да биде валидна IPv4 адреса.',
    'ipv6' => 'Полето :attribute мора да биде валидна IPv6 адреса.',
    'json' => 'Полето :attribute мора да биде валиден JSON стринг.',
    'list' => 'Полето :attribute мора да биде листа.',
    'lowercase' => 'Полето :attribute мора да биде со мали букви.',
    'lt' => [
        'array' => 'Полето :attribute мора да има помалку од :value елементи.',
        'file' => 'Полето :attribute мора да биде помало од :value килобајти.',
        'numeric' => 'Полето :attribute мора да биде помало од :value.',
        'string' => 'Полето :attribute мора да има помалку од :value карактери.',
    ],
    'lte' => [
        'array' => 'Полето :attribute не смее да има повеќе од :value елементи.',
        'file' => 'Полето :attribute мора да биде помало или еднакво на :value килобајти.',
        'numeric' => 'Полето :attribute мора да биде помало или еднакво на :value.',
        'string' => 'Полето :attribute мора да има :value карактери или помалку.',
    ],
    'mac_address' => 'Полето :attribute мора да биде валидна MAC адреса.',
    'max' => [
        'array' => 'Полето :attribute не смее да има повеќе од :max елементи.',
        'file' => 'Полето :attribute не смее да биде поголемо од :max килобајти.',
        'numeric' => 'Полето :attribute не смее да биде поголемо од :max.',
        'string' => 'Полето :attribute не смее да има повеќе од :max карактери.',
    ],
    'max_digits' => 'Полето :attribute не смее да има повеќе од :max цифри.',
    'mimes' => 'Полето :attribute мора да биде датотека од тип: :values.',
    'mimetypes' => 'Полето :attribute мора да биде датотека од тип: :values.',
    'min' => [
        'array' => 'Полето :attribute мора да има најмалку :min елементи.',
        'file' => 'Полето :attribute мора да биде најмалку :min килобајти.',
        'numeric' => 'Полето :attribute мора да биде најмалку :min.',
        'string' => 'Полето :attribute мора да има најмалку :min карактери.',
    ],
    'min_digits' => 'Полето :attribute мора да има најмалку :min цифри.',
    'missing' => 'Полето :attribute мора да недостасува.',
    'missing_if' => 'Полето :attribute мора да недостасува кога :other е :value.',
    'missing_unless' => 'Полето :attribute мора да недостасува освен ако :other не е :value.',
    'missing_with' => 'Полето :attribute мора да недостасува кога :values е присутно.',
    'missing_with_all' => 'Полето :attribute мора да недостасува кога :values се присутни.',
    'multiple_of' => 'Полето :attribute мора да биде повеќекратник на :value.',
    'not_in' => 'Избраната вредност за :attribute е невалидна.',
    'not_regex' => 'Форматот на полето :attribute е невалиден.',
    'numeric' => 'Полето :attribute мора да биде број.',
    'password' => [
        'letters' => 'Полето :attribute мора да содржи најмалку една буква.',
        'mixed' => 'Полето :attribute мора да содржи најмалку една голема и една мала буква.',
        'numbers' => 'Полето :attribute мора да содржи најмалку еден број.',
        'symbols' => 'Полето :attribute мора да содржи најмалку еден симбол.',
        'uncompromised' => 'Дадената :attribute се појавила во истекување на податоци. Ве молиме изберете друга :attribute.',
    ],
    'present' => 'Полето :attribute мора да биде присутно.',
    'present_if' => 'Полето :attribute мора да биде присутно кога :other е :value.',
    'present_unless' => 'Полето :attribute мора да биде присутно освен ако :other не е :value.',
    'present_with' => 'Полето :attribute мора да биде присутно кога :values е присутно.',
    'present_with_all' => 'Полето :attribute мора да биде присутно кога :values се присутни.',
    'prohibited' => 'Полето :attribute е забрането.',
    'prohibited_if' => 'Полето :attribute е забрането кога :other е :value.',
    'prohibited_unless' => 'Полето :attribute е забрането освен ако :other не е во :values.',
    'prohibits' => 'Полето :attribute забранува :other да биде присутно.',
    'regex' => 'Форматот на полето :attribute е невалиден.',
    'required' => 'Полето :attribute е задолжително.',
    'required_array_keys' => 'Полето :attribute мора да содржи записи за: :values.',
    'required_if' => 'Полето :attribute е задолжително кога :other е :value.',
    'required_if_accepted' => 'Полето :attribute е задолжително кога :other е прифатено.',
    'required_if_declined' => 'Полето :attribute е задолжително кога :other е одбиено.',
    'required_unless' => 'Полето :attribute е задолжително освен ако :other не е во :values.',
    'required_with' => 'Полето :attribute е задолжително кога :values е присутно.',
    'required_with_all' => 'Полето :attribute е задолжително кога :values се присутни.',
    'required_without' => 'Полето :attribute е задолжително кога :values не е присутно.',
    'required_without_all' => 'Полето :attribute е задолжително кога ниедно од :values не е присутно.',
    'same' => 'Полињата :attribute и :other мора да се совпаѓаат.',
    'size' => [
        'array' => 'Полето :attribute мора да содржи :size елементи.',
        'file' => 'Полето :attribute мора да биде :size килобајти.',
        'numeric' => 'Полето :attribute мора да биде :size.',
        'string' => 'Полето :attribute мора да има :size карактери.',
    ],
    'starts_with' => 'Полето :attribute мора да почнува со едно од следниве: :values.',
    'string' => 'Полето :attribute мора да биде стринг.',
    'timezone' => 'Полето :attribute мора да биде валидна временска зона.',
    'unique' => 'Полето :attribute веќе е зафатено.',
    'uploaded' => 'Полето :attribute не успеа да се прикачи.',
    'uppercase' => 'Полето :attribute мора да биде со големи букви.',
    'url' => 'Полето :attribute мора да биде валиден URL.',
    'ulid' => 'Полето :attribute мора да биде валиден ULID.',
    'uuid' => 'Полето :attribute мора да биде валиден UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute". This makes it quick to specify a specific
    | custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'име',
        'email' => 'емаил',
        'phone' => 'телефон',
        'password' => 'лозинка',
        'password_confirmation' => 'потврда на лозинка',
    ],

];