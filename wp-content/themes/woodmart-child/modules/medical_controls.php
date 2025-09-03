<?php

define('MEDICAL_CONTROL_FIELDS', [
    'controles' => [
        'block_name' => 'Controles',
        'block_fields' => [
            'control_date' => [
                'type' => 'text',
                'input_type' => 'date',
                'id' => 'control_date',
                'name' => 'control_date',
                'class' => '',
                'label' => 'Fecha',
                'required' => true,
                'extra_attr' => [
                    'data-required_message' => 'El Campo Fecha es requerido',
                ],
            ],
        ],
    ],
]);