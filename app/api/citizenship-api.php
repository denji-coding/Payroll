<?php
header('Content-Type: application/json');

// List of countries and their citizenship demonyms (short version for demo, expand as needed)
$citizenships = [
    ['country' => 'Afghanistan', 'citizenship' => 'Afghan'],
    ['country' => 'Albania', 'citizenship' => 'Albanian'],
    ['country' => 'Algeria', 'citizenship' => 'Algerian'],
    ['country' => 'Argentina', 'citizenship' => 'Argentine'],
    ['country' => 'Australia', 'citizenship' => 'Australian'],
    ['country' => 'Austria', 'citizenship' => 'Austrian'],
    ['country' => 'Bangladesh', 'citizenship' => 'Bangladeshi'],
    ['country' => 'Belgium', 'citizenship' => 'Belgian'],
    ['country' => 'Brazil', 'citizenship' => 'Brazilian'],
    ['country' => 'Canada', 'citizenship' => 'Canadian'],
    ['country' => 'China', 'citizenship' => 'Chinese'],
    ['country' => 'Denmark', 'citizenship' => 'Danish'],
    ['country' => 'Egypt', 'citizenship' => 'Egyptian'],
    ['country' => 'Philippines', 'citizenship' => 'Filipino'],
    ['country' => 'Finland', 'citizenship' => 'Finnish'],
    ['country' => 'France', 'citizenship' => 'French'],
    ['country' => 'Germany', 'citizenship' => 'German'],
    ['country' => 'Greece', 'citizenship' => 'Greek'],
    ['country' => 'India', 'citizenship' => 'Indian'],
    ['country' => 'Indonesia', 'citizenship' => 'Indonesian'],
    ['country' => 'Italy', 'citizenship' => 'Italian'],
    ['country' => 'Japan', 'citizenship' => 'Japanese'],
    ['country' => 'Malaysia', 'citizenship' => 'Malaysian'],
    ['country' => 'Mexico', 'citizenship' => 'Mexican'],
    ['country' => 'Netherlands', 'citizenship' => 'Dutch'],
    ['country' => 'New Zealand', 'citizenship' => 'New Zealander'],
    ['country' => 'Nigeria', 'citizenship' => 'Nigerian'],
    ['country' => 'Norway', 'citizenship' => 'Norwegian'],
    ['country' => 'Pakistan', 'citizenship' => 'Pakistani'],  
    ['country' => 'Poland', 'citizenship' => 'Polish'],
    ['country' => 'Portugal', 'citizenship' => 'Portuguese'],
    ['country' => 'Russia', 'citizenship' => 'Russian'],
    ['country' => 'Saudi Arabia', 'citizenship' => 'Saudi'],
    ['country' => 'Singapore', 'citizenship' => 'Singaporean'],
    ['country' => 'South Africa', 'citizenship' => 'South African'],
    ['country' => 'South Korea', 'citizenship' => 'South Korean'],
    ['country' => 'Spain', 'citizenship' => 'Spanish'],
    ['country' => 'Sweden', 'citizenship' => 'Swedish'],
    ['country' => 'Switzerland', 'citizenship' => 'Swiss'],
    ['country' => 'Thailand', 'citizenship' => 'Thai'],
    ['country' => 'Turkey', 'citizenship' => 'Turkish'],
    ['country' => 'Ukraine', 'citizenship' => 'Ukrainian'],
    ['country' => 'United Arab Emirates', 'citizenship' => 'Emirati'],
    ['country' => 'United Kingdom', 'citizenship' => 'British'],
    ['country' => 'United States', 'citizenship' => 'American'],
    ['country' => 'Vietnam', 'citizenship' => 'Vietnamese'],
    // ... add more as needed
];

echo json_encode([
    'status' => 'success',
    'data' => $citizenships
]);