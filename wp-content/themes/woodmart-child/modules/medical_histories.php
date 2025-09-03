<?php
$current_user_id = get_current_user_id();
define('MEDICAL_HISTORY_FIELDS', [
    'medico' => [
        'block_name' => 'Medico',
        'block_fields' => [],
    ],
    'otros-diagnosticos' =>[
        'block_name'=>'otros-diagnosticos',
        'block_fields'=>[],
    ],
    'anamnesis' => [
        'block_name' => 'Anamnesis',
        'block_fields' => [
            'ID' => [
                'type' => 'hidden',
                'id' => 'ID',
                'name' => 'ID',
                'label' => '',
            ],
            'receta_id' => [
                'type' => 'hidden',
                'id' => 'receta_id',
                'name' => 'receta_id',
                'label' => '',
            ],
            'title_anamnesis' => [
                'type' => 'html',
                'id' => 'title_anamnesis',
                'name' => 'title_anamnesis',
                'html' => '<h2 class="h2-style">Anamnesis</h2>',
            ],
            'consultation_date' => [
                'type' => 'text',
                'input_type' => 'date',
                'id' => 'consultation_date',
                'name' => 'consultation_date',
                'class' => '',
                'label' => 'Fecha',
                'required' => true,
                'extra_attr' => [
                    'data-required_message' => 'El Campo Fecha es requerido',
                ],
                'field_width' => '10%',
            ],
            'consultation_reason' => [
                'type' => 'textarea',
                'id' => 'consultation_reason',
                'name' => 'consultation_reason',
                'label' => 'Motivo',
                'rows' => 3,
                'field_width' => '30%',
                'can_hide'=> true,
            ],
        ],
    ],
    'antecedentes-ginecologicos' => [
        'block_name' => 'ANTECEDENTES GINECOLÓGICOS',
        'block_fields' => [
            'title_antecedentes_ginecologicos' => [
                'name' => 'title_antecedentes_ginecologicos',
                'label' => 'Antecedentes Ginecológicos',
                'type' => 'html',
                'html' => '<h3 class="h3-style">Antecedentes Ginecológicos</h3>',
                'can_hide' => true,
            ],
            'last_menstruation_date' => [
                'name' => 'last_menstruation_date',
                'label' => 'Fecha de última menstruación',
                'type' => 'datepicker',
                'class' => 'gender-woman',
                'can_hide' => true,
                'field_width' => '33.333333%',
            ],
            'gestation' => [
                'name' => 'gestation',
                'label' => 'Gestación',
                'type' => 'number',
                'class' => 'gender-woman',
                'can_hide' => true,
                'field_width' => '33.333333%',
            ],
            'paridad' => [
                'name' => 'paridad',
                'label' => 'Paridad',
                'type' => 'number',
                'class' => 'gender-woman',
                'can_hide' => true,
                'field_width' => '33.333333%',
            ],
            'obstetric_formula_head' => [
                'name' => 'obstetric_formula_head',
                'label' => 'Fórmula Obstétrica Imagen con Titulo',
                'type' => 'html',
                'html' => '
                <div>
                    <div>
                        <img style="max-width:350px;" src="' . get_theme_file_uri('img/FormulaObstetrica.png') . '">
                    </div>
                    <label class="cx-label">Fórmula Obstétrica</label>
                </div>',
                'can_hide' => true,
            ],
            'obstetric_formula_first_label' => [
                'name' => 'obstetric_formula_first_label',
                'label' => 'Fórmula Obstétrica G',
                'type' => 'html',
                'html' => '<label class="cx-label">G</label>',
                'can_hide' => true,
            ],
            'obstetric_formula_1' => [
                'name' => 'obstetric_formula_1',
                'label' => 'Fórmula Obstétrica',
                'type' => 'number',
                'class' => 'gender-woman',
                'can_hide' => true,
            ],
            'obstetric_formula_second_label' => [
                'name' => 'obstetric_formula_second_label',
                'label' => 'Fórmula Obstétrica P',
                'type' => 'html',
                'html' => '<label class="cx-label">P</label>',
                'can_hide' => true,
            ],
            'obstetric_formula_2' => [
                'name' => 'obstetric_formula_2',
                'label' => 'Fórmula Obstétrica 2',
                'type' => 'number',
                'class' => 'gender-woman',
                'can_hide' => true,
            ],
            'obstetric_formula_3' => [
                'name' => 'obstetric_formula_3',
                'label' => 'Fórmula Obstétrica 3',
                'type' => 'number',
                'class' => 'gender-woman',
                'can_hide' => true,
            ],
            'obstetric_formula_4' => [
                'name' => 'obstetric_formula_4',
                'label' => 'Fórmula Obstétrica 4',
                'type' => 'number',
                'class' => 'gender-woman',
                'can_hide' => true,
            ],
            'obstetric_formula_5' => [
                'name' => 'obstetric_formula_5',
                'label' => 'Fórmula Obstétrica 5',
                'type' => 'number',
                'class' => 'gender-woman',
                'can_hide' => true,
            ],
            'catamenial_regimen' => [
                'name' => 'catamenial_regimen',
                'label' => 'Regimen Catamenial',
                'type' => 'text',
                'class' => 'gender-woman',
                'can_hide' => true,
            ],
        ],
    ],
    'examen-fisico-general' => [
        'block_name' => 'Examen Físico General',
        'block_fields' => [
            'title_examen_fisico_general' => [
                'name' => 'title_examen_fisico_general',
                'label' => 'Examen Físico General',
                'type' => 'html',
                'html' => '<h2 class="h2-style">Examen Físico General</h2>',
                'can_hide' => true,
            ],
            'weight' => [
                'name' => 'weight',
                'label' => 'Peso (Kg)',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '15%',
            ],
            'height' => [
                'name' => 'height',
                'label' => 'Talla (m)',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '15%',
            ],
            'imc' => [
                'name' => 'imc',
                'label' => 'IMC',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '15%',
            ],
            'creatina' => [
                'name' => 'creatina',
                'label' => 'Creatinina',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '15%',
            ],
            'indice_filtrado_glomerular' => [
                'name' => 'indice_filtrado_glomerular',
                'label' => 'IFGe',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '15%',
            ],
            'body_surface_area' => [
                'name' => 'body_surface_area',
                'label' => 'ASC (m²)',
                'type' => 'text',
                'can_hide' => true,
                'field_width' => '15%',
            ],
            'auxiliary_exams' => [
                'name' => 'auxiliary_exams',
                'label' => 'Exámenes Auxiliares',
                'type' => 'text',
                'can_hide' => true,
            ],
        ],
    ],
    'diagnostico' => [
        'block_name' => 'Diagnóstico',
        'block_fields' => [
            'title_diagnostico' => [
                'type' => 'html',
                'label' => 'Impresión diagnóstica',
                'name' => 'title_diagnostico',
                'html' => '<h2 class="h2-style">Impresión diagnóstica</h2>',
                'can_hide' => true,
            ],
            'cie10' => [
                'name' => 'cie10',
                'label' => 'CIE-10',
                'type' => 'cie10_multiple',
                'can_hide' => true,
            ],
            'diagnosis_observations' => [
                'name' => 'diagnosis_observations',
                'label' => 'Otros Diagnósticos no especificados / Descripción del Diágnóstico',
                'type' => 'textarea',
                'can_hide' => true,
            ],
            'treatment' => [
                'name' => 'treatment',
                'label' => 'Tratamiento',
                'type' => 'textarea',
                'can_hide' => true,
            ],
            'plan_to_follow' => [
                'name' => 'plan_to_follow',
                'label' => 'Plan a seguir',
                'type' => 'textarea',
                'can_hide' => true,
            ],
            'title_fechas' => [
                'type' => 'title',
                'name' => 'title_fechas',
                'label' => 'Fechas',
                'can_hide' => true,
            ],
            'next_appointment_date' => [
                'name' => 'next_appointment_date',
                'label' => ($current_user_id === 609 ? 'Traumatología' : 'Cita'),
                'type' => 'text',
                'class' => 'cx-label fecha-cita',
                'input_type' => 'datetime-local',
                'extra_attr' => [
                    'data-datetime-settings' => htmlspecialchars(json_encode([
                        'timeFormat' => 'HH:mm',
                        'altTimeFormat' => 'HH:mm',
                        'altSeparator' => ' ',
                    ])),
                ],
                'can_hide' => true,
                'field_width' => '33.333333%',
            ],
            'next_control_date' => [
                'name' => 'next_control_date',
                'label' => ($current_user_id === 609 ? 'Medicina Física' : 'Control'),
                'type' => 'text',
                'class' => 'cx-label fecha-control',
                'input_type' => 'datetime-local',
                'extra_attr' => [
                    'data-datetime-settings' => htmlspecialchars(json_encode([
                        'timeFormat' => 'HH:mm',
                        'altTimeFormat' => 'HH:mm',
                        'altSeparator' => ' ',
                    ])),
                ],
                'can_hide' => true,
                'field_width' => '33.333333%',
            ],
            'surgery_date' => [
                'name' => 'surgery_date',
                'label' => ($current_user_id === 609 ? 'Fisioterapia' : 'Procedimiento y/o Intervención'),
                'type' => 'text',
                'class' => 'cx-label fecha-procedimiento',
                'input_type' => 'datetime-local',
                'extra_attr' => [
                    'data-datetime-settings' => htmlspecialchars(json_encode([
                        'timeFormat' => 'HH:mm',
                        'altTimeFormat' => 'HH:mm',
                        'altSeparator' => ' ',
                    ])),
                ],
                'can_hide' => true,
                'field_width' => '33.333333%',
            ],
        ],
    ],
    'fisioterapia' => [
        'block_name' => 'Fisioterapia	',
        'block_fields' => [
            'title_diagnosticos' => [
                'type' => 'html',
                'label' => 'Impresión diagnóstica',
                'name' => 'title_diagnostico',
                'html' => '<h2 class="h2-style">Impresión diagnóstica</h2>',
                'can_hide' => true,
            ],
            'cie10' => [
                'name' => 'cie10',
                'label' => 'Diagnóstico Médico',
                'type' => 'cie10_multiple',
            ],
            'fisio_diagnosis_observations' => [
                'name' => 'diagnosis_observations',
                'label' => 'Diagnóstico Fisioterapéutico',
                'type' => 'textarea',
                'can_hide' => true,
            ],
            'controles' => [
                'name' => 'controles',
                'label' => 'Controles',
                'type' => 'textarea',
                'can_hide' => true,
            ],
        ],
    ],
]);
