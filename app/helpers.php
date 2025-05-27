<?php

if (!function_exists('getCategoryIcon')) {
    function getCategoryIcon($category)
    {
        $icons = [
            'electronics' => 'fas fa-mobile-alt',
            'clothing' => 'fas fa-tshirt',
            'accessories' => 'fas fa-ring',
            'bags' => 'fas fa-briefcase',
            'keys' => 'fas fa-key',
            'documents' => 'fas fa-file-alt',
            'other' => 'fas fa-question-circle'
        ];

        return $icons[strtolower($category)] ?? 'fas fa-question-circle';
    }
}