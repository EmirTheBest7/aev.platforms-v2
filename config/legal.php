<?php

declare(strict_types=1);

use Core\Helpers\Env;

/**
 * Legal pages (/privacy, /imprint). NOTHING here is legal advice or a real company detail — the owner provides it.
 *
 * operator: who runs the site. Values come from the environment (LEGAL_*), never from the repository.
 *           `required` fields that are empty are shown as "to be provided by the owner" and the page stays noindex.
 * privacy:  the policy as sections of paragraphs. An empty `body` renders the placeholder notice for that section.
 *           Write the real text here (plain paragraphs, no HTML) once it has been drafted and reviewed.
 *           A factual inventory of what the site processes is in docs/LEGAL.md.
 */
return [
    'operator' => [
        'name' => ['label' => 'Operator / controller', 'value' => Env::get('LEGAL_OPERATOR_NAME', ''), 'required' => true],
        'address' => ['label' => 'Registered address', 'value' => Env::get('LEGAL_OPERATOR_ADDRESS', ''), 'required' => true],
        'registration_id' => ['label' => 'Company / registration number', 'value' => Env::get('LEGAL_REGISTRATION_ID', ''), 'required' => true],
        'representative' => ['label' => 'Represented by', 'value' => Env::get('LEGAL_REPRESENTATIVE', ''), 'required' => false],
        'register_entry' => ['label' => 'Register entry', 'value' => Env::get('LEGAL_REGISTER_ENTRY', ''), 'required' => false],
        'vat_id' => ['label' => 'VAT ID', 'value' => Env::get('LEGAL_VAT_ID', ''), 'required' => false],
    ],

    'privacy' => [
        'updated' => '',
        'sections' => [
            ['heading' => 'Who is responsible', 'body' => []],
            ['heading' => 'What data we process and why', 'body' => []],
            ['heading' => 'Legal bases', 'body' => []],
            ['heading' => 'Recipients and service providers', 'body' => []],
            ['heading' => 'Transfers outside the EU/EEA', 'body' => []],
            ['heading' => 'Retention', 'body' => []],
            ['heading' => 'Cookies and similar technologies', 'body' => []],
            ['heading' => 'Your rights', 'body' => []],
            ['heading' => 'Complaints and contact', 'body' => []],
            ['heading' => 'Changes to this policy', 'body' => []],
        ],
    ],
];
